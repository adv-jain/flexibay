<?php

namespace App\Filament\Resources\Rooms;

use App\Filament\Resources\Rooms\Pages\CreateRoom;
use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Filament\Resources\Rooms\Pages\ListRooms;
use App\Filament\Resources\Rooms\Pages\ViewRoom;
use App\Filament\Resources\Rooms\Schemas\RoomForm;
use App\Filament\Resources\Rooms\Schemas\RoomInfolist;
use App\Filament\Resources\Rooms\Tables\RoomsTable;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
// FIXED: 5.x unified schema uses this precise injection namespace layout
use Filament\Schemas\Components\Utilities\Set;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                // 1. The Filtered Property Dropdown Field
                Select::make('property_id')
                    ->relationship(
                        name: 'property',
                        titleAttribute: 'title',
                        modifyQueryUsing: function (Builder $query) {
                            $user = Auth::user();

                            // Admins see all properties to choose from
                            if ($user?->hasRole('admin')) {
                                return $query;
                            }

                            // Managers see their own properties + their staff's properties
                            if ($user?->hasRole('manager')) {
                                $staffIds = User::where('parent_id', $user->id)
                                    ->pluck('id')
                                    ->toArray();
                                $allowedIds = array_merge([$user->id], $staffIds);
                                return $query->whereIn('user_id', $allowedIds);
                            }

                            // Staff members see properties belonging to their manager, themselves, or fellow staff
                            if ($user?->hasRole('staff')) {
                                $managerId = $user->parent_id;
                                $staffIds = \App\Models\User::where('parent_id', $managerId)->pluck('id')->toArray();
                                // FIXED: Ensured both manager ID, current staff ID, and peer staff IDs are merged safely
                                $allowedIds = array_merge([$managerId, $user->id], $staffIds);
                                return $query->whereIn('user_id', array_unique($allowedIds));
                            }

                            // Absolute Fallback: Block visibility safely if role check encounters an anomaly
                            return $query->whereKey(null);
                        }
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()

                    // 2. Automating the Inheritance Logic
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        if (blank($state)) {
                            $set('user_id', null);
                            return;
                        }

                        // Look up the selected property's owner ID
                        $propertyOwnerId = Property::where('id', $state)->value('user_id');

                        // Silently set the hidden room 'user_id' field to match the owner
                        $set('user_id', $propertyOwnerId);
                    }),

                // 3. The Hidden User ID Receiver Field
                Hidden::make('user_id')
                    ->required(),

                // 4. Unpack all custom room fields cleanly from the external layout schema file
                ...RoomForm::configureFields(),

            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoomInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user?->hasRole('admin')) {
            return $query;
        }

        if ($user?->hasRole('manager')) {
            $staffIds = \App\Models\User::where('parent_id', $user->id)->pluck('id')->toArray();
            $allowedUserIds = array_merge([$user->id], $staffIds);

            return $query->whereIn('user_id', $allowedUserIds);
        }

        // FIXED: Added team scoping query layout filter block for staff role
        if ($user?->hasRole('staff')) {
            $managerId = $user->parent_id;

            // Fetch all peer staff IDs working for the same manager
            $staffIds = \App\Models\User::where('parent_id', $managerId)->pluck('id')->toArray();
            $allowedUserIds = array_merge([$managerId, $user->id], $staffIds);

            return $query->whereIn('user_id', array_unique($allowedUserIds));
        }

        return $query->whereKey(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'view' => ViewRoom::route('/{record}'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }
}

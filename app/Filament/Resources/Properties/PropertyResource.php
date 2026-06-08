<?php

namespace App\Filament\Resources\Properties;

use App\Filament\Resources\Properties\Pages\CreateProperty;
use App\Filament\Resources\Properties\Pages\EditProperty;
use App\Filament\Resources\Properties\Pages\ListProperties;
use App\Filament\Resources\Properties\Pages\ViewProperty;
use App\Filament\Resources\Properties\Schemas\PropertyForm;
use App\Filament\Resources\Properties\Schemas\PropertyInfolist;
use App\Filament\Resources\Properties\Tables\PropertiesTable;
use App\Models\Property;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'title'; // FIXED: Changed to valid string record title layout target column

    public static function form(Schema $schema): Schema
    {
        return PropertyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PropertyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // If user object profile is corrupt or blank, drop early exit safety validation block
        if (! $user) {
            return $query->whereKey(null);
        }

        // 1. Admins see all properties globally across the ecosystem
        if ($user->hasRole('admin')) {
            return $query;
        }

        // 2. Managers see their own properties + properties created by their staff
        if ($user->hasRole('manager')) {
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            $allowedUserIds = array_merge([$user->id], $staffIds);

            return $query->whereIn('user_id', $allowedUserIds);
        }

        // 3. FIXED: Staff members see properties owned by their manager or fellow team peers
        if ($user->hasRole('staff')) {
            $managerId = $user->parent_id;

            // Fetch all staff members belonging to the same manager
            $staffIds = User::where('parent_id', $managerId)->pluck('id')->toArray();

            // Build the team ID visibility envelope pool safely
            $allowedUserIds = array_merge([$managerId, $user->id], $staffIds);

            return $query->whereIn('user_id', array_unique($allowedUserIds));
        }

        // Fallback safety barrier check rule
        return $query->whereKey(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProperties::route('/'),
            'create' => CreateProperty::route('/create'),
            'view' => ViewProperty::route('/{record}'),
            'edit' => EditProperty::route('/{record}/edit'),
        ];
    }
}

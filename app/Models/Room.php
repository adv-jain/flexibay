<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Room extends Model
{
    //
    protected $fillable = [
        'property_id',
        'room_type',
        'title',
        'capacity',
        'price',
        'total_inventory',
        'featured_room_image',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    //
    protected $fillable = [
        'is_available',
        'user_id',
        'title',
        'description',
        'featured_image',
        'image_option_1',
        'image_option_2',
        'image_option_3',
        'image_option_4',
        'price',
        'combo_breakfast_lunch_dinner',
        'with_breakfast',
        'address',
        'city',
        'state',
        'postal_code',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Guest;

class Booking extends Model
{
    protected $fillable = [
        'guest_id',
        'booking_reference',
        'room_number',
        'room_id',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'room_price',
        'tax_amount',
        'total_amount',
        'booking_platform',
        'booking_status',
        'payment_status',
        'payment_method',
        'special_requests',
        'internal_notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'room_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
}

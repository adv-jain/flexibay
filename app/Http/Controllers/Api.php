<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingSyncController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'woocommerce_order_id' => 'required|integer',
            'customer_name'        => 'required|string',
            'email'                => 'required|email',
            'phone'                => 'nullable|string',
            'checkin'              => 'required|date',
            'checkout'             => 'required|date',
            'room_id'              => 'nullable|integer', // WP room post ID
            'hostel'               => 'nullable|string',
            'addons'               => 'nullable|string',
            'amount'               => 'required|numeric',
            'status'               => 'required|string',
        ]);

        // Prevent duplicate syncs for same WooCommerce order
        $existing = Booking::where('woocommerce_order_id', $data['woocommerce_order_id'])->first();

        if ($existing) {
            // Just update status if order already synced
            $existing->update([
                'booking_status' => $this->mapStatus($data['status']),
            ]);

            return response()->json(['message' => 'updated', 'id' => $existing->id]);
        }

        // Match room by WP room post ID stored on the room model
        $room = Room::where('wp_room_id', $data['room_id'])->first();

        $booking = Booking::create([
            'woocommerce_order_id' => $data['woocommerce_order_id'],
            'booking_reference'    => 'WC-' . $data['woocommerce_order_id'],
            'room_id'              => $room?->id,
            'room_number'          => $room?->room_number,
            'property_id'          => $room?->property_id,
            'customer_name'        => $data['customer_name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'],
            'check_in_date'        => $data['checkin'],
            'check_out_date'       => $data['checkout'],
            'total_amount'         => $data['amount'],
            'booking_platform'     => 'website',
            'booking_status'       => $this->mapStatus($data['status']),
            'payment_status'       => 'paid',
            'internal_notes'       => $data['addons'] ? 'Add-ons: ' . $data['addons'] : null,
        ]);

        return response()->json(['message' => 'created', 'id' => $booking->id], 201);
    }

    private function mapStatus(string $wcStatus): string
    {
        return match ($wcStatus) {
            'processing' => 'confirmed',
            'completed'  => 'confirmed',
            'cancelled'  => 'cancelled',
            'refunded'   => 'cancelled',
            default      => 'pending',
        };
    }
}

<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Facades\DB;  // ← add this

class BookingObserver
{
  public function created(Booking $booking): void
  {
    if (!$booking->room_id) return; // ← guard

    $this->recalculate($booking->room_id);
  }

  public function updated(Booking $booking): void
  {
    if (!$booking->room_id) return; // ← guard

    if ($booking->wasChanged('booking_status') && $booking->booking_status === 'cancelled') {
      $this->recalculate($booking->room_id);
    }
  }

  public function deleted(Booking $booking): void
  {
    if (!$booking->room_id) return; // ← guard

    $this->recalculate($booking->room_id);
  }

  private function recalculate(int $roomId): void
  {
    $room = Room::find($roomId);
    if (!$room) return;

    $currentGuests = Booking::where('room_id', $roomId)
      ->whereNotIn('booking_status', ['cancelled', 'checked_out'])
      ->where('check_out_date', '>=', now()->toDateString())
      ->sum(DB::raw('adults + children'));

    $room->update([
      'current_guests' => $currentGuests,
      'is_available'   => $currentGuests < $room->capacity,
    ]);
  }
}

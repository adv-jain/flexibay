<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;  // ← add this

class ReleaseExpiredBookings extends Command
{
  protected $signature   = 'bookings:release-expired';
  protected $description = 'Reset room capacity for bookings whose checkout date has passed';

  public function handle(): void
  {
    $expiredRoomIds = Booking::where('check_out_date', '<', now()->toDateString())
      ->whereNotIn('booking_status', ['cancelled', 'checked_out'])
      ->pluck('room_id')
      ->unique();

    foreach ($expiredRoomIds as $roomId) {
      $room = Room::find($roomId);
      if (!$room) continue;

      $currentGuests = Booking::where('room_id', $roomId)
        ->whereNotIn('booking_status', ['cancelled', 'checked_out'])
        ->where('check_out_date', '>=', now()->toDateString())
        ->sum(DB::raw('adults + children'));  // ← fixed

      $room->update([
        'current_guests' => $currentGuests,
        'is_available'   => $currentGuests < $room->capacity,
      ]);
    }

    $this->info("Released capacity for {$expiredRoomIds->count()} rooms.");
  }
}

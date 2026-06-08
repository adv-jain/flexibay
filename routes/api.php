use App\Http\Controllers\Api\BookingSyncController;

Route::post('/bookings/sync', [BookingSyncController::class, 'store'])
    ->middleware('api.key');
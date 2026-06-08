<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Booking Details
            $table->string('booking_reference')->unique();
            $table->string('booking_platform')->nullable();

            $table->string('room_number');

            $table->date('check_in_date');
            $table->date('check_out_date');

            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);

            // Pricing
            $table->decimal('room_price', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            // Status
            $table->string('booking_status')->default('pending');
            $table->string('payment_status')->default('unpaid');

            // Extra Details
            $table->string('payment_method')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

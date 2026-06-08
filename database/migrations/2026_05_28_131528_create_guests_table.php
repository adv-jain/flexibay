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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();

            $table->string('photo')->nullable();

            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            // Identity Verification
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();

            $table->string('id_document_front')->nullable();
            $table->string('id_document_back')->nullable();

            // Stay Information
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();

            $table->string('room_number')->nullable();
            $table->string('booking_reference')->nullable();

            $table->text('special_requests')->nullable();
            $table->text('internal_notes')->nullable();

            // Optional Extras
            $table->string('guest_photo')->nullable();
            $table->string('company_name')->nullable();
            $table->string('gst_number')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};

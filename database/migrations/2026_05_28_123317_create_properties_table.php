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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            // Modern, constrained foreign key referencing the users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('image_option_1')->nullable();
            $table->string('image_option_2')->nullable();
            $table->string('image_option_3')->nullable();
            $table->string('image_option_4')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('combo_breakfast_lunch_dinner', 10, 2)->nullable();
            $table->decimal('with_breakfast', 10, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            // Geographic coordinates (nullable in case address lookup fails)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

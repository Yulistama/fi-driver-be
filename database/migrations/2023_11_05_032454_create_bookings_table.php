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
            $table->string('name');
            $table->integer('status_id');
            $table->integer('staff_id');
            $table->integer('driver_id');
            $table->integer('pickup_city_id');
            $table->integer('destination_city_id');
            $table->string('pickup_address');
            $table->string('destination_address');
            $table->timestamp('estimated_pickup_time');
            $table->timestamp('estimated_finish_time');
            $table->string('note')->nullable();
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

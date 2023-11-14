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
        Schema::table('history_status_bookings', function (Blueprint $table) {
            // Change column name
            $table->integer('name')->change();
            $table->renameColumn('name', 'status_history_id');
            $table->string('description')->nullable()->change();

            // Add a new field
            $table->string('location')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_status_bookings', function (Blueprint $table) {
            // Reverse the changes made in the up method
            $table->renameColumn('name', 'status_history_id')->integer();
            $table->dropColumn('location');
        });
    }
};

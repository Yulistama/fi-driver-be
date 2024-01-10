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
        Schema::table('users', function (Blueprint $table) {
            $table->string('subjek_status')->nullable()->after('gender_id');
            $table->timestamp('date_start_status')->nullable()->after('subjek_status');
            $table->timestamp('date_finish_status')->nullable()->after('date_start_status');
            $table->string('reason')->nullable()->after('date_finish_status');
            $table->string('attachment')->nullable()->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

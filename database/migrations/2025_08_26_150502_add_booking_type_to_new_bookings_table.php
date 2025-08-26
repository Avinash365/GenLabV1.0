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
        Schema::table('new_bookings', function (Blueprint $table) {
            $table->enum('booking_type', ['pay', 'without_pay'])
                  ->default('without_pay')
                  ->after('hold_status'); // adjust position as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            Schema::table('new_bookings', function (Blueprint $table) {
                $table->dropColumn('booking_type');
            });
        });
    }
};

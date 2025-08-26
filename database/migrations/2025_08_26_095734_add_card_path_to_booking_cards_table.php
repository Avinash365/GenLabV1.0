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
        Schema::table('booking_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->after('id');
            $table->foreign('booking_id')
                  ->references('id')->on('new_bookings')
                  ->onDelete('cascade');
            $table->string('card_path')->after('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_cards', function (Blueprint $table) {
             $table->dropForeign(['booking_id']);
             $table->dropColumn('booking_id');
             $table->dropColumn('card_path');
        });
    }
};

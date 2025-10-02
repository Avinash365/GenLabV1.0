<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_item_report', function (Blueprint $table) {
            // add constraint for booking_item_id
            $table->foreign('booking_item_id')
                  ->references('id')
                  ->on('booking_items')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('booking_item_report', function (Blueprint $table) {
            $table->dropForeign(['booking_item_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->boolean('is_canceled')->default(false)->after('cancel_reason');
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropColumn('is_canceled');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->timestamp('cancel_at')->nullable()->after('hold_reason');
            $table->text('cancel_reason')->nullable()->after('cancel_at');
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropColumn(['cancel_at', 'cancel_reason']);
        });
    }
};

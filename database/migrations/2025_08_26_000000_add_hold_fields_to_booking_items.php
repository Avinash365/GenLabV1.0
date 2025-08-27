<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->timestamp('hold_at')->nullable()->after('received_at');
            $table->text('hold_reason')->nullable()->after('hold_at');
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropColumn(['hold_at', 'hold_reason']);
        });
    }
};

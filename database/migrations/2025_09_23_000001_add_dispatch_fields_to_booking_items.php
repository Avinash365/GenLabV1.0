<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'dispatched_at')) {
                $table->dateTime('dispatched_at')->nullable()->after('received_at');
            }
            if (!Schema::hasColumn('booking_items', 'dispatched_by_id')) {
                $table->unsignedBigInteger('dispatched_by_id')->nullable()->after('dispatched_at');
            }
            if (!Schema::hasColumn('booking_items', 'dispatched_by_name')) {
                $table->string('dispatched_by_name')->nullable()->after('dispatched_by_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            if (Schema::hasColumn('booking_items', 'dispatched_by_name')) {
                $table->dropColumn('dispatched_by_name');
            }
            if (Schema::hasColumn('booking_items', 'dispatched_by_id')) {
                $table->dropColumn('dispatched_by_id');
            }
            if (Schema::hasColumn('booking_items', 'dispatched_at')) {
                $table->dropColumn('dispatched_at');
            }
        });
    }
};

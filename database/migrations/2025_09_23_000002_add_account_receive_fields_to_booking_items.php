<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'account_received_at')) {
                $table->dateTime('account_received_at')->nullable()->after('received_at');
            }
            if (!Schema::hasColumn('booking_items', 'account_received_by_id')) {
                $table->unsignedBigInteger('account_received_by_id')->nullable()->after('account_received_at');
            }
            if (!Schema::hasColumn('booking_items', 'account_received_by_name')) {
                $table->string('account_received_by_name')->nullable()->after('account_received_by_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            if (Schema::hasColumn('booking_items', 'account_received_by_name')) {
                $table->dropColumn('account_received_by_name');
            }
            if (Schema::hasColumn('booking_items', 'account_received_by_id')) {
                $table->dropColumn('account_received_by_id');
            }
            if (Schema::hasColumn('booking_items', 'account_received_at')) {
                $table->dropColumn('account_received_at');
            }
        });
    }
};

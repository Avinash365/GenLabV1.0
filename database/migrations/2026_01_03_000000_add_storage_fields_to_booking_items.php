<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'storage_no')) {
                $table->string('storage_no', 100)->nullable()->after('job_order_no');
            }
            if (!Schema::hasColumn('booking_items', 'storage_description')) {
                $table->text('storage_description')->nullable()->after('storage_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            if (Schema::hasColumn('booking_items', 'storage_description')) {
                $table->dropColumn('storage_description');
            }
            if (Schema::hasColumn('booking_items', 'storage_no')) {
                $table->dropColumn('storage_no');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('booking_items')) return;
        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'submitted_to')) {
                $table->string('submitted_to', 191)->nullable()->after('dispatch_comment');
            }
            if (!Schema::hasColumn('booking_items', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('submitted_to');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('booking_items')) return;
        Schema::table('booking_items', function (Blueprint $table) {
            if (Schema::hasColumn('booking_items', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('booking_items', 'submitted_to')) {
                $table->dropColumn('submitted_to');
            }
        });
    }
};

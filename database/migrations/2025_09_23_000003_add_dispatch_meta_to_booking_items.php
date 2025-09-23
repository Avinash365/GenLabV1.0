<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'dispatch_by')) {
                $table->string('dispatch_by')->nullable()->after('dispatched_by_name');
            }
            if (!Schema::hasColumn('booking_items', 'dispatch_person_name')) {
                $table->string('dispatch_person_name')->nullable()->after('dispatch_by');
            }
            if (!Schema::hasColumn('booking_items', 'dispatch_assignment_no')) {
                $table->string('dispatch_assignment_no')->nullable()->after('dispatch_person_name');
            }
            if (!Schema::hasColumn('booking_items', 'dispatch_comment')) {
                $table->text('dispatch_comment')->nullable()->after('dispatch_assignment_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            foreach (['dispatch_comment','dispatch_assignment_no','dispatch_person_name','dispatch_by'] as $col) {
                if (Schema::hasColumn('booking_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

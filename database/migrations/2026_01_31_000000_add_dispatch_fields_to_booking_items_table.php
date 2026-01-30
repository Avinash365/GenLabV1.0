<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('booking_items')) {
            return;
        }

        // Add dispatch meta fields if they don't exist
        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'dispatch_by')) {
                $table->string('dispatch_by', 100)->nullable()->after('dispatched_by_name');
            }
            if (!Schema::hasColumn('booking_items', 'dispatch_person_name')) {
                $table->string('dispatch_person_name', 150)->nullable()->after('dispatch_by');
            }
            if (!Schema::hasColumn('booking_items', 'dispatch_assignment_no')) {
                $table->string('dispatch_assignment_no', 100)->nullable()->after('dispatch_person_name');
            }
            if (!Schema::hasColumn('booking_items', 'dispatch_comment')) {
                $table->text('dispatch_comment')->nullable()->after('dispatch_assignment_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('booking_items')) {
            return;
        }

        Schema::table('booking_items', function (Blueprint $table) {
            if (Schema::hasColumn('booking_items', 'dispatch_comment')) {
                $table->dropColumn('dispatch_comment');
            }
            if (Schema::hasColumn('booking_items', 'dispatch_assignment_no')) {
                $table->dropColumn('dispatch_assignment_no');
            }
            if (Schema::hasColumn('booking_items', 'dispatch_person_name')) {
                $table->dropColumn('dispatch_person_name');
            }
            if (Schema::hasColumn('booking_items', 'dispatch_by')) {
                $table->dropColumn('dispatch_by');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReferenceNoUniqueInNewBookings extends Migration
{
    public function up()
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            // drop old unique index
            $table->dropUnique(['reference_no']);

            // add new composite unique index for soft delete
            $table->unique(['reference_no', 'deleted_at'], 'new_bookings_ref_no_deleted_at_unique');
        });
    }

    public function down()
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            // remove composite unique
            $table->dropUnique('new_bookings_ref_no_deleted_at_unique');

            // restore original unique constraint
            $table->unique('reference_no');
        });
    }
}

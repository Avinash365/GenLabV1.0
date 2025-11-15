<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMsColumnToNewBookingsTable extends Migration
{
    public function up()
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            $table->string('m_s', 150)->nullable()->after('upload_letter_path');
        });
    }

    public function down()
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            $table->dropColumn('m_s');
        });
    }
}

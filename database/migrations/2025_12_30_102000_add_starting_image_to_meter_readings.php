<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->string('starting_image_path')->nullable()->after('starting_at');
        });
    }

    public function down()
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropColumn('starting_image_path');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            if (! Schema::hasColumn('meter_readings', 'ending_image_path')) {
                $table->string('ending_image_path')->nullable()->after('ending_at');
            }
        });
    }

    public function down()
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            if (Schema::hasColumn('meter_readings', 'ending_image_path')) {
                $table->dropColumn('ending_image_path');
            }
        });
    }
};

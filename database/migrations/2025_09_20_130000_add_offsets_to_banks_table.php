<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->integer('offset_top')->default(0)->after('cheque_image_path');
            $table->integer('offset_left')->default(0)->after('offset_top');
        });
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['offset_top', 'offset_left']);
        });
    }
};

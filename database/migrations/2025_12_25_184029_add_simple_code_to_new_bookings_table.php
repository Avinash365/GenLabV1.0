<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            $table->string('sample_code', 50)
                  ->nullable()
                  ->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_bookings', function (Blueprint $table) {
             $table->dropColumn('sample_code');
        });
    }
};

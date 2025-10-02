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
            $table->date('letter_date')->nullable()->after('upload_letter_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            $table->dropColumn('letter_date');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_item_report', function (Blueprint $table) {
            $table->string('ult_r_no')->nullable()->after('pdf_path');
            $table->dateTime('date_of_start_of_analysis')->nullable()->after('ult_r_no');
            $table->dateTime('date_of_completion_of_analysis')->nullable()->after('date_of_start_of_analysis');
            $table->dateTime('date_of_receipt')->nullable()->after('date_of_completion_of_analysis');
        });
    }

    public function down(): void
    {
        Schema::table('booking_item_report', function (Blueprint $table) {
            $table->dropColumn([
                'ult_r_no',
                'date_of_start_of_analysis',
                'date_of_completion_of_analysis',
                'date_of_receipt',
            ]);
        });
    }
};

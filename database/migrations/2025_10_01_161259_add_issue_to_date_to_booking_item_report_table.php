<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_item_report', function (Blueprint $table) {
            $table->dateTime('issue_to_date')->nullable()->after('date_of_receipt');
        });
    }

    public function down(): void
    {
        Schema::table('booking_item_report', function (Blueprint $table) {
            $table->dropColumn('issue_to_date');
        });
    }
};

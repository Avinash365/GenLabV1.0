<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lab_report_overrides') && !Schema::hasColumn('lab_report_overrides', 'letter_ref')) {
            Schema::table('lab_report_overrides', function (Blueprint $table) {
                $table->text('letter_ref')->nullable()->after('completion_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lab_report_overrides') && Schema::hasColumn('lab_report_overrides', 'letter_ref')) {
            Schema::table('lab_report_overrides', function (Blueprint $table) {
                $table->dropColumn('letter_ref');
            });
        }
    }
};

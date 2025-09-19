<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_formats', function (Blueprint $table) {
            if (!Schema::hasColumn('report_formats', 'body_html')) {
                $table->longText('body_html')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('report_formats', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('body_html');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_formats', function (Blueprint $table) {
            if (Schema::hasColumn('report_formats', 'version')) {
                $table->dropColumn('version');
            }
            if (Schema::hasColumn('report_formats', 'body_html')) {
                $table->dropColumn('body_html');
            }
        });
    }
};

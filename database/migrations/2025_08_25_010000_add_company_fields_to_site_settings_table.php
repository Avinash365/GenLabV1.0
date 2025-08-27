<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) return;
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'company_name')) {
                $table->string('company_name')->nullable()->after('theme_color');
            }
            if (!Schema::hasColumn('site_settings', 'company_address')) {
                $table->text('company_address')->nullable()->after('company_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_settings')) return;
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'company_address')) {
                $table->dropColumn('company_address');
            }
            if (Schema::hasColumn('site_settings', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });
    }
};
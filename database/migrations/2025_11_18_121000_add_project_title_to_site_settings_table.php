<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'project_title')) {
                $table->string('project_title')->nullable()->after('company_address');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'project_title')) {
                $table->dropColumn('project_title');
            }
        });
    }
};

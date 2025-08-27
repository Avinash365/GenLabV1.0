<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('company_name')->nullable();
                $table->text('company_address')->nullable();
                $table->string('site_logo')->nullable();
                $table->string('theme')->default('system');
                $table->string('primary_color', 7)->default('#0d6efd');
                $table->timestamps();
            });
            return;
        }

        if (!Schema::hasColumn('settings', 'theme')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('theme')->default('system');
            });
        }
        if (!Schema::hasColumn('settings', 'primary_color')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('primary_color', 7)->default('#0d6efd');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        // If the table only contains columns created above, drop it; otherwise, just drop the added columns if they exist.
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'theme')) {
                $table->dropColumn('theme');
            }
            if (Schema::hasColumn('settings', 'primary_color')) {
                $table->dropColumn('primary_color');
            }
        });
    }
};

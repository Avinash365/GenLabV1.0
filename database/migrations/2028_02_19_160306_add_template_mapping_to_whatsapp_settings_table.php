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
        if (!Schema::hasColumn('whatsapp_settings', 'hold_template_name')) {
            Schema::table('whatsapp_settings', function (Blueprint $table) {
                $table->string('hold_template_name')->nullable();
            });
        }

        if (!Schema::hasColumn('whatsapp_settings', 'report_template_name')) {
            Schema::table('whatsapp_settings', function (Blueprint $table) {
                $table->string('report_template_name')->nullable();
            });
        }

        if (!Schema::hasColumn('whatsapp_settings', 'dispatch_template_name')) {
            Schema::table('whatsapp_settings', function (Blueprint $table) {
                $table->string('dispatch_template_name')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('whatsapp_settings', 'hold_template_name') ||
            Schema::hasColumn('whatsapp_settings', 'report_template_name') ||
            Schema::hasColumn('whatsapp_settings', 'dispatch_template_name')) {
            Schema::table('whatsapp_settings', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('whatsapp_settings', 'hold_template_name')) {
                    $cols[] = 'hold_template_name';
                }
                if (Schema::hasColumn('whatsapp_settings', 'report_template_name')) {
                    $cols[] = 'report_template_name';
                }
                if (Schema::hasColumn('whatsapp_settings', 'dispatch_template_name')) {
                    $cols[] = 'dispatch_template_name';
                }

                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

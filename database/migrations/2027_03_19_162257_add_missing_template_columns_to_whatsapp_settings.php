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
        // Schema::table('whatsapp_settings', function (Blueprint $table) {
        //     $table->string('hold_template_name')->nullable()->after('default_template_name');
        //     $table->string('report_template_name')->nullable()->after('hold_template_name');
        //     $table->string('dispatch_template_name')->nullable()->after('report_template_name');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('whatsapp_settings', function (Blueprint $table) {
        //     $table->dropColumn(['hold_template_name', 'report_template_name', 'dispatch_template_name']);
        // });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'footer_credit')) {
                    $table->string('footer_credit')->nullable()->after('project_title');
                }
                if (!Schema::hasColumn('site_settings', 'footer_copyright')) {
                    $table->text('footer_copyright')->nullable()->after('footer_credit');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'footer_copyright')) {
                    $table->dropColumn('footer_copyright');
                }
                if (Schema::hasColumn('site_settings', 'footer_credit')) {
                    $table->dropColumn('footer_credit');
                }
            });
        }
    }
};

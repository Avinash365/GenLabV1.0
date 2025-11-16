<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->string('approval_summary_path')->nullable()->after('approval_note');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->dropColumn('approval_summary_path');
        });
    }
};

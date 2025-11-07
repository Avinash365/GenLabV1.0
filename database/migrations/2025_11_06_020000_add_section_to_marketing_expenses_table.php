<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->enum('section', ['marketing','office'])->default('marketing')->after('marketing_person_code');
            $table->index(['section','status']);
        });
    }

    public function down(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->dropIndex(['section','status']);
            $table->dropColumn('section');
        });
    }
};

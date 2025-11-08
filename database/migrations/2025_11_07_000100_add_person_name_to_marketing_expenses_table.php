<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->string('person_name')->nullable()->after('marketing_person_code');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->dropColumn('person_name');
        });
    }
};

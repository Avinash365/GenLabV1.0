<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->date('issue_date')->nullable()->after('status'); // adjust 'after' as needed
        });
    }

    public function down(): void
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->dropColumn('issue_date');
        });
    }
};
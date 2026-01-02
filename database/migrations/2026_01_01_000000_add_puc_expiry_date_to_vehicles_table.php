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
        if (!Schema::hasColumn('vehicles', 'puc_expiry_date')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->date('puc_expiry_date')->nullable()->after('puc_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'puc_expiry_date')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('puc_expiry_date');
            });
        }
    }
};

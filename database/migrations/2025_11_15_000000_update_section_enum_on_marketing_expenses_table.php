<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `marketing_expenses` MODIFY `section` ENUM('marketing','office','personal') NOT NULL DEFAULT 'marketing'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `marketing_expenses` MODIFY `section` ENUM('marketing','office') NOT NULL DEFAULT 'marketing'");
    }
};

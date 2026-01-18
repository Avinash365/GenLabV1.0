<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing FK, make column nullable, recreate FK with ON DELETE SET NULL
        Schema::table('important_letters', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });

        // Modify column to be nullable
        DB::statement('ALTER TABLE important_letters MODIFY uploaded_by BIGINT UNSIGNED NULL');

        Schema::table('important_letters', function (Blueprint $table) {
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('important_letters', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });

        DB::statement('ALTER TABLE important_letters MODIFY uploaded_by BIGINT UNSIGNED NOT NULL');

        Schema::table('important_letters', function (Blueprint $table) {
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });

        DB::statement('ALTER TABLE documents MODIFY uploaded_by BIGINT UNSIGNED NULL');

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });

        DB::statement('ALTER TABLE documents MODIFY uploaded_by BIGINT UNSIGNED NOT NULL');

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};

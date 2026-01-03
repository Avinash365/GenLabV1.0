<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['generated_by']);

            // Make column nullable
            $table->foreignId('generated_by')
                ->nullable()
                ->change();

            // Re-add foreign key with cascade
            $table->foreign('generated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Drop FK again
            $table->dropForeign(['generated_by']);

            // Make NOT NULL
            $table->foreignId('generated_by')
                ->nullable(false)
                ->change();

            // Re-add FK
            $table->foreign('generated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};

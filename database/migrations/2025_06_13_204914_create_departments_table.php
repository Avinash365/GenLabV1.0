<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique()->comment('Full name of the department');
            $table->json('codes')->comment('Array of 3-4 letter codes for department');
            $table->text('description')->nullable()->comment('Optional description');

            $table->boolean('is_active')->default(true)->index()->comment('Department active status');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

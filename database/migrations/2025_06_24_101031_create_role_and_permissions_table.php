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
        Schema::create('role_and_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('type')->nullable();
            $table->string('group')->nullable();
            $table->string('group_slug')->nullable();
            $table->string('group_icon')->nullable();
            $table->string('group_color')->nullable();
            $table->string('group_type')->nullable();
            $table->string('group_description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Optionally, add foreign keys if users table exists
            // $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            // $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_and_permissions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibrations', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name');
            $table->string('equipment_name');
            $table->date('issue_date');
            $table->date('expire_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign key to users (optional, if you have User model)
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibrations');
    }
};

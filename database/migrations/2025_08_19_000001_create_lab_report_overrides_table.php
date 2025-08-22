<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_report_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('format'); // e.g., AAC_BLOCK.legacy.php
            $table->string('reference_no'); // e.g., ULR_NO or any reference
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('results')->nullable();
            $table->text('conformity')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['format', 'reference_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_report_overrides');
    }
};

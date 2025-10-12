<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_item_report_28day', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('booking_item_id');
            $table->unsignedBigInteger('report_editor_file_id')->nullable();
            $table->string('generated_report_path', 255)->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->string('ult_r_no', 255)->nullable();
            $table->dateTime('date_of_start_of_analysis')->nullable();
            $table->dateTime('date_of_completion_of_analysis')->nullable();
            $table->dateTime('date_of_receipt')->nullable();
            $table->dateTime('issue_to_date')->nullable();
            $table->timestamps();

            // Foreign keys (same as original table)
            $table->foreign('booking_item_id')
                ->references('id')
                ->on('booking_items')
                ->onDelete('cascade');

            $table->foreign('report_editor_file_id')
                ->references('id')
                ->on('report_editor_files')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_item_report_28day');
    }
};

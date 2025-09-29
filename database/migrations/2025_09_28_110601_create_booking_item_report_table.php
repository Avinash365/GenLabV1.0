<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_item_report', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('booking_item_id');
            $table->unsignedBigInteger('report_editor_file_id')->nullable();
            $table->string('generated_report_path')->nullable();
            $table->string('pdf_path')->nullable(); 

            $table->foreign('report_editor_file_id')
                            ->references('id')->on('report_editor_files')
                            ->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_item_report');
    }
};

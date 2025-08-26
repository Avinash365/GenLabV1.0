<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_booking_id')
                  ->constrained('new_bookings')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->string('lab_analysis_code');
            $table->foreign('lab_analysis_code')
                    ->references('user_code')
                    ->on('users')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

            $table->string('job_order_no', 50);
            
            $table->string('sample_quality', 100);
            $table->decimal('amount', 50,2)->nullable()->unsigned(); 
            
            $table->string('particulars', 100)->nullable(); 
            $table->string('sample_description', 255);
            
            $table->date('lab_expected_date');
            $table->timestamps();
            $table->softDeletes();

            $table->index('new_booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};

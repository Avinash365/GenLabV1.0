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
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to new_bookings table
            $table->foreignId('new_booking_id')
                  ->constrained('new_bookings')
                  ->onDelete('cascade');
            
            // Item fields
            $table->string('sample_description');
            $table->string('sample_quality');
            $table->date('lab_expected_date');
            $table->decimal('amount', 10, 2);
            $table->string('lab_analysis');
            $table->string('job_order_no');
            
            $table->timestamps();
            
            // Optional: Add index for better performance
            $table->index('new_booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
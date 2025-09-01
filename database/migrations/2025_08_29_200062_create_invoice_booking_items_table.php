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
        Schema::create('invoice_booking_items', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('invoice_booking_id'); 
            $table->string('invoice_no'); 
            $table->string('job_order_no'); 
            $table->integer('qty')->default(1); 
            $table->decimal('rate', 10, 2)->default(0.00); 
            $table->text('sample_discription')->nullable(); 
            
            $table->timestamps(); 

            // Foreign key constraint
            $table->foreign('invoice_booking_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('cascade'); // Optional: deletes items if parent invoice deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_booking_items');
    }
};

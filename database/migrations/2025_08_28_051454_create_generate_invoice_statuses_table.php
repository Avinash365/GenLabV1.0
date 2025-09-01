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
        Schema::create('booking_invoice_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_booking_id')
                  ->constrained('new_bookings')
                  ->cascadeOnDelete(); 

            $table->boolean('generate_invoice_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generate_invoice_statuses');
    }
};

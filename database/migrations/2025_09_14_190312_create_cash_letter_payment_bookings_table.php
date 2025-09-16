<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_letter_payment_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_letter_payment_id');
            $table->unsignedBigInteger('booking_id');
            $table->string('payment_status')->default('pending');
            $table->timestamps();

            $table->foreign('cash_letter_payment_id')->references('id')->on('cash_letter_payments')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('new_bookings')->onDelete('cascade');

            // Shorter unique constraint name
            $table->unique(['cash_letter_payment_id', 'booking_id'], 'cl_payment_booking_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_letter_payment_bookings');
    }
};

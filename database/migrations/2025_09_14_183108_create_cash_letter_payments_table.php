<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_letter_payments', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('client_id');
            $table->string('marketing_person_id');

            // Other fields
            $table->text('booking_ids'); // store multiple booking IDs as comma-separated or JSON
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_mode'); // cash, cheque, online, etc.
            $table->date('transaction_date');
            $table->decimal('amount_received', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('marketing_person_id')->references('user_code')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_letter_payments');
    }
};

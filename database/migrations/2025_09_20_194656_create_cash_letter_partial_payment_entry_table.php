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
        Schema::create('cash_letter_partial_payment_entry', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->unsignedBigInteger('client_id');
            $table->string('marketing_person_id'); // assuming it's user_code
            $table->unsignedBigInteger('cash_letter_payment_id');

            // Payment info
            $table->enum('payment_mode', ['cash', 'cheque', 'online', 'account_transfer', 'upi']);
            $table->date('transaction_date');
            $table->decimal('amount_received', 15, 2);
            $table->text('note')->nullable();

            // Metadata
            $table->string('created_by');
            $table->timestamps(); // created_at and updated_at

            // Foreign key constraints
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('marketing_person_id')->references('user_code')->on('users')->onDelete('cascade');
            $table->foreign('cash_letter_payment_id')->references('id')->on('cash_letter_payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_letter_partial_payment_entry');
    }
};

<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('marketing_person_id');
            $table->foreign('marketing_person_id')
                    ->references('user_code')
                    ->on('users')
                    ->onDelete('cascade');


            $table->decimal('amount_received', 12, 2);  
            $table->string('payment_mode')->nullable(); // e.g., cash, bank, UPI
            $table->date('transaction_date')->nullable();
            $table->string('transaction_reference')->nullable(); // bank ref/UPI ID
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_transactions');
    }
};

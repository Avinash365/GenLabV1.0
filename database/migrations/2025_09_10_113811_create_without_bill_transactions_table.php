<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('without_bill_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->decimal('total_amount', 10, 2);   // total for month
            $table->decimal('paid_amount', 10, 2);    // amount paid
            $table->decimal('due_amount', 10, 2)->default(0); // unpaid
            $table->decimal('carry_forward', 10, 2)->default(0); // carried to next month
            $table->enum('payment_mode', ['cash','online','upi']);
            $table->string('reference')->nullable(); // transaction ID / UPI Ref
            $table->date('payment_month'); // which month this payment is for
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('without_bill_transactions');
    }
};

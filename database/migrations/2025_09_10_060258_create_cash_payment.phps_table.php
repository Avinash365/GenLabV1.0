<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->date('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->json('invoice_ids'); // Store array of invoice IDs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_payments');
    }
};

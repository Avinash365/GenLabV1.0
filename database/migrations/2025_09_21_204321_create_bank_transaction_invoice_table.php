<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transaction_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_transaction_id');
            $table->string('invoice_no');

            $table->foreign('bank_transaction_id')->references('id')->on('bank_transactions')->onDelete('cascade');

            $table->unique(['bank_transaction_id', 'invoice_no']); // avoid duplicate invoice linking
             $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transaction_invoice');
    }
};

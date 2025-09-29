<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transaction_ref', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_transaction_id');
            $table->string('ref_no');

            $table->foreign('bank_transaction_id')->references('id')->on('bank_transactions')->onDelete('cascade');

            $table->unique(['bank_transaction_id', 'ref_no']); // prevent duplicates
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transaction_ref');
    }
};

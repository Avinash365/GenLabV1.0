<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transaction_client', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_transaction_id');
            $table->unsignedBigInteger('client_id');

            $table->foreign('bank_transaction_id')->references('id')->on('bank_transactions')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->unique(['bank_transaction_id', 'client_id']); // avoid duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transaction_client');
    }
};

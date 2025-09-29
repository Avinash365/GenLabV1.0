<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('tran_id')->nullable();
            $table->string('transaction_remarks')->nullable();
            $table->string('chq_ref_no')->nullable();
            $table->date('value_date')->nullable();
            $table->decimal('withdrawal', 15, 2)->nullable();
            $table->decimal('deposit', 15, 2)->nullable();
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->text('note')->nullable();

            $table->string('marketing_person')->nullable(); // only one per transaction

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('bank')->nullable();
            $table->string('cheque_no')->index();
            $table->string('payee_name');
            $table->date('date')->nullable();
            $table->string('purpose')->nullable();
            $table->string('handed_over_to')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('amount_in_words')->nullable();
            $table->enum('status', ['issued','received','cancelled'])->default('issued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};

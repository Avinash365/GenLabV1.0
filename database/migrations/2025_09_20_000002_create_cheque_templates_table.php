<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cheque_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->string('field_name'); // payee_name, date, amount_number, amount_words
            $table->unsignedInteger('top')->default(0); // px
            $table->unsignedInteger('left')->default(0); // px
            $table->unsignedTinyInteger('font_size')->default(14); // px
            $table->timestamps();
            $table->unique(['bank_id', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque_templates');
    }
};

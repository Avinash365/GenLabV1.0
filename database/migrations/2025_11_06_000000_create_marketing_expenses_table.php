<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketing_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('marketing_person_code'); // references users.user_code
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('from_date');
            $table->date('to_date');
            $table->string('file_path')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->index('marketing_person_code');
            $table->index(['status','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_expenses');
    }
};

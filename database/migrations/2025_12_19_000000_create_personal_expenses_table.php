<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Make migration safe to run when the table already exists (avoid duplicate-create error)
        if (Schema::hasTable('personal_expenses')) {
            return;
        }

        Schema::create('personal_expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_code', 100)->index();
            $table->string('section')->nullable();
            $table->date('expense_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('approved_amount', 12, 2)->nullable()->default(0);
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->boolean('submitted_for_approval')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_expenses');
    }
};

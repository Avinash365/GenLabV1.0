<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cleared_expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->decimal('approved_total', 14, 2)->default(0);
            $table->decimal('total_expenses', 14, 2)->default(0);
            $table->string('approved_section')->nullable();
            $table->string('person_name')->nullable();
            $table->string('person_code')->nullable();
            $table->json('meta')->nullable();
            $table->json('expense_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cleared_expenses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manual_invoice_payment', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no')->index();
            $table->date('invoice_generated_date');
            $table->decimal('total_amount', 12, 2);

            $table->date('payment_date')->nullable();

            // 0 = unpaid, 1 = paid
            $table->tinyInteger('status')
                  ->default(0)
                  ->comment('0=Unpaid, 1=Paid');

            // Marketing person (users.user_code)
            $table->string('marketing_person')->index();

            // Created by (users.user_code)
            $table->string('created_by')->index()->nullable()
                  ->comment('user_code of creator');

            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('manual_invoice_payment');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tds_payments', function (Blueprint $table) {
            $table->id();

            // Invoice reference
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Client reference (nullable for set null on delete)
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // Marketing person references users.user_code
            $table->string('marketing_person_id')->nullable();

            $table->decimal('tds_percentage', 5, 2);
            $table->decimal('amount_after_tds', 12, 2);
            $table->enum('payment_mode', ['cash','cheque','online','account_transfer','upi']);
            $table->date('transaction_date');
            $table->decimal('amount_received', 12, 2);
            $table->text('notes')->nullable();

            // Created by (nullable for set null)
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('marketing_person_id')
                ->references('user_code')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tds_payments');
    }
};

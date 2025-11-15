<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('leave_deductions', 14, 2)->default(0);
            $table->decimal('other_deductions', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->date('payout_due_date')->nullable();
            $table->timestamp('payout_released_at')->nullable();
            $table->text('remarks')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['payroll_cycle_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};

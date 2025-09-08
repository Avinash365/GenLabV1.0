<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->text('instructions')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('branch')->nullable();
            $table->string('branch_holder_name')->nullable(); // ✅ new field
            $table->string('ifsc_code')->nullable();
            $table->string('pan_code')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('gstin')->nullable();
            $table->string('upi')->nullable();

            // user tracking
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->nullOnDelete();

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};

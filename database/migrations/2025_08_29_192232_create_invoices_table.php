<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('new_booking_id')
                    ->nullable()->constrained('new_bookings')->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('generated_by')
                    ->nullable()
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
        
            $table->string('invoice_no')->unique();                     // Invoice number
            $table->string('type')->nullable(); 
            $table->string('issue_to')->nullable();                     // Reference number
            $table->date('letter_date')->nullable();                    // Letter date
            $table->string('name_of_work')->nullable();                 // Work/Project name
            $table->string('client_gstin')->nullable();                 // Client GSTIN
            $table->string('sac_code')->nullable();                     // SAC Code
            $table->string('invoice_letter_path')->nullable();          // Path to invoice letter
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->decimal('cgst_percent', 5, 2)->default(0.00);
            $table->decimal('sgst_percent', 5, 2)->default(0.00);
            $table->decimal('igst_percent', 5, 2)->default(0.00);
            $table->boolean('round_of')->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0.00);  
            $table->decimal('total_amount', 12, 2)->default(0.00);
                        
            $table->timestamps();                                      // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

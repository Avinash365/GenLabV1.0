<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blank_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('marketing_person')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('reference_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('letter_date')->nullable();
            $table->string('name_of_work')->nullable();
            $table->string('bill_issue_to')->nullable();
            $table->string('client_gstin')->nullable();
            $table->text('address')->nullable();

            // Totals
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('after_discount', 15, 2)->default(0);
            $table->decimal('cgst_percent', 5, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_percent', 5, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_percent', 5, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('round_off', 15, 2)->default(0);
            $table->decimal('payable_amount', 15, 2)->default(0);

            $table->enum('invoice_type', ['tax_invoice', 'proforma_invoice'])->default('tax_invoice');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blank_invoices');
    }
};

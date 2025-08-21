<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stock_entries', function (Blueprint $table) {
            $table->id();

            // Foreign key to products table (product_code must exist there)
            $table->string('product_code');
            $table->foreign('product_code')
                  ->references('product_code')
                  ->on('products')
                  ->onDelete('cascade');

            // Stock entry details
            $table->decimal('purchase_price', 10, 2)->nullable()->unsigned();
            $table->integer('quantity')->nullable()->unsigned();
            $table->string('purchase_unit')->nullable(); // 👈 New field (unit type)
            $table->text('remarks')->nullable();

            // File upload path (bill copy, PDF, image, etc.)
            $table->string('upload_bill')->nullable();

            // Unique invoice number
            $table->string('invoice_no')->unique();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['product_code', 'invoice_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_entries');
    }
};

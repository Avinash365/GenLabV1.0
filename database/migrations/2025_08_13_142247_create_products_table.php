<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Correct foreign key to product_categories
            $table->unsignedBigInteger('product_category_id');
            $table->foreign('product_category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('restrict'); // safer than cascade

            $table->string('invoice_no')->unique();
            $table->string('product_code')->unique();
            $table->string('product_name');     
            $table->decimal('purchase_price', 10, 2);
            $table->string('purchase_unit'); // e.g., kg, gram
            $table->integer('unit'); // number
            $table->text('remark')->nullable();

            // Polymorphic relation: either admin or user
            $table->unsignedBigInteger('created_by_id');
            $table->string('created_by_type');

            $table->timestamps(); 
            $table->softDeletes();  // deleted_at

            $table->index(['created_by_id', 'created_by_type', 'product_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

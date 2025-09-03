<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blank_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blank_invoice_id')->constrained()->onDelete('cascade');
            $table->string('description')->nullable();
            $table->string('job_order_no')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blank_invoice_items');
    }
};

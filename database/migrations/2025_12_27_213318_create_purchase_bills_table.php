<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();

            $table->string('description')->nullable();
            $table->string('party')->nullable();
            $table->decimal('amount', 12, 2)->default(0);

            $table->string('purchased_by')->nullable();

            $table->string('bill_upload')->nullable(); 
            // store file path

            $table->enum('gst_type', ['GST', 'Non-GST'])->default('Non-GST');

            $table->date('purchase_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};

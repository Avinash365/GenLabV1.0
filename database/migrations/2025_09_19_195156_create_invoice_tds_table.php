<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_tds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('marketing_person_id'); 
            $table->foreign('marketing_person_id')
                    ->references('user_code')
                    ->on('users')
                    ->onDelete('cascade');


            $table->decimal('tds_percentage', 5, 2)->nullable(); 
            $table->decimal('tds_amount', 12, 2)->nullable();
            $table->decimal('amount_after_tds', 15,2)->nullable(); 
            $table->timestamps();

            //  one TDS entry pevoice (can be removed later if multiple needed)
            $table->unique('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_tds');
    }
};

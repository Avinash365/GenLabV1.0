<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('important_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')            // Maps to user who uploaded
                  ->constrained('users')
                  ->onDelete('cascade');


            $table->string('department_name');              // Which department issued/receives the letter
            $table->string('client_name');                  // Client or receiver name
            $table->string('letter_no')->nullable();        // Title or subject of letter
            $table->text('sample')->nullable();             // Any reference/sample text
            $table->string('file_path')->nullable();    
            
            $table->enum('status', ['send', 'archived'])->default('send'); 
            $table->date('letter_data')->nullable(); 
            $table->string('remarks')->nullable(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('important_letters');
    }
};

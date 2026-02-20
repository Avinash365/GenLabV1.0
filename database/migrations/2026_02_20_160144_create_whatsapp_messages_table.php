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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('meta_message_id', 255)->nullable()->unique();
            $table->string('phone_number', 50)->comment('Sender phone number');
            $table->string('receiver_number', 50)->comment('Business account number');
            $table->text('message')->nullable();
            $table->string('type', 50)->default('text'); // text, image, document, location, etc.
            $table->string('status', 50)->default('received'); // sent, delivered, read, received
            $table->json('media_info')->nullable(); // For storing image/doc details
            $table->timestamp('meta_timestamp')->nullable(); // Timestamp from Meta
            $table->text('raw_data')->nullable(); // Store raw webhook payload for debugging
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email', 255);
            $table->string('password', 255);
            $table->string('smtp_host', 255)->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('imap_host', 255)->nullable();
            $table->integer('imap_port')->nullable();
            $table->string('encryption', 10)->nullable();
            $table->timestamps();

            // Optional: add foreign key to users table (if applicable)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};

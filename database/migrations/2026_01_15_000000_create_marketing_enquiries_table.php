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
        Schema::create('marketing_enquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_item_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note');
            $table->json('media_paths')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_enquiries');
    }
};

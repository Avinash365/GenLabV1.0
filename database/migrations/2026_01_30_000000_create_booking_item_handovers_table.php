<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_item_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_item_id')->constrained('booking_items')->onDelete('cascade');
            $table->string('client_name', 255);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('handed_over_by_id')->nullable();
            $table->string('handed_over_by_name', 255)->nullable();
            $table->timestamp('handed_over_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_item_handovers');
    }
};

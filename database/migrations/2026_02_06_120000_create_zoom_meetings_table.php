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
        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->dateTime('start_time');
            $table->integer('duration')->comment('minutes');
            $table->text('agenda')->nullable(); // discussed topic
            $table->string('join_url')->nullable();
            $table->string('start_url')->nullable(); // for host
            $table->string('meeting_id')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->default('waiting'); // waiting, started, finished
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->text('start_description')->nullable();
            $table->decimal('starting_reading', 15, 4)->nullable();
            $table->timestamp('starting_at')->nullable();

            $table->text('end_description')->nullable();
            $table->decimal('ending_reading', 15, 4)->nullable();
            $table->timestamp('ending_at')->nullable();

            $table->string('image_path')->nullable();
            $table->decimal('total_reading', 15, 4)->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meter_readings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_booking_id')
                  ->constrained('new_bookings')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->string('sample_description', 255);
            $table->string('sample_quality', 100);
            $table->date('lab_expected_date');
            $table->decimal('amount', 10, 2);
            $table->string('lab_analysis', 255);
            $table->string('job_order_no', 50);

            $table->timestamps();
            $table->softDeletes();
            $table->index('new_booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};

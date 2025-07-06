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
        Schema::create('new_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_name');
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->date('job_order_date');
            $table->string('report_issue_to');
            $table->string('reference_no');
            $table->string('marketing_code');
            $table->string('contact_no');
            $table->string('contact_email');
            $table->string('contractor_name');
            $table->boolean('hold_status')->default(false);
            $table->string('upload_letter_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_bookings');
    }
};
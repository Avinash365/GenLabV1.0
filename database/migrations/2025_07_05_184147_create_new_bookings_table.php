<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('new_bookings', function (Blueprint $table) {
            $table->id();

            // foreign key to users table
            $table->unsignedBigInteger('marketing_id'); 

            $table->string('client_name', 150);
            $table->text('client_address')->nullable();
            $table->date('job_order_date');
            $table->string('report_issue_to', 150);
            $table->string('reference_no', 50)->unique();
            $table->string('contact_no', 20);
            $table->string('contact_email', 150);
            $table->string('contractor_name', 150);

            $table->boolean('hold_status')->default(false);

            $table->string('upload_letter_path', 255)->nullable();

            // Polymorphic relation: either admin or user
            $table->unsignedBigInteger('created_by_id');
            $table->string('created_by_type');

            $table->timestamps();
            $table->softDeletes();

            // Add foreign key
            $table->foreign('marketing_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); 

            $table->index(['created_by_id', 'created_by_type', 'client_name']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('new_bookings');
    }
};

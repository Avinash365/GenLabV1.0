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
            $table->foreignId('admin_id')
                  ->constrained('admins')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->string('client_name', 150);
            $table->text('client_address')->nullable();
            $table->string('client_email', 150)->nullable();
            $table->string('client_phone', 20)->nullable();
            $table->date('job_order_date');
            $table->string('report_issue_to', 150);
            $table->string('reference_no', 50)->unique();
            $table->string('marketing_code', 50);
            $table->string('contact_no', 20);
            $table->string('contact_email', 150);
            $table->string('contractor_name', 150);
            $table->boolean('hold_status')->default(false);
            $table->string('upload_letter_path', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('new_bookings');
    }
};

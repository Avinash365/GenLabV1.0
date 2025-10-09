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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('employee_name');
            $table->enum('leave_type', ['Sick Leave', 'Casual Leave', 'Emergency Leave', 'Annual Leave', 'Maternity Leave', 'Paternity Leave']);
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('days_hours'); // Number of days or hours
            $table->enum('day_type', ['Full Day', 'Half Day', 'Hours'])->default('Full Day');
            $table->text('reason');
            $table->enum('status', ['Applied', 'Approved', 'Rejected'])->default('Applied');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_comments')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};

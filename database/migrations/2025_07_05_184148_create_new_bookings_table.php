<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewBookingsTable extends Migration
{
    public function up()
    {
        // Skip if table already exists (pre-existing DB)
        if (Schema::hasTable('new_bookings')) {
            return;
        }

        Schema::create('new_bookings', function (Blueprint $table) {
            $table->id();
            
            //foreign key
            $table->string('marketing_id', 255);  
            
            $table->foreignId('department_id')
                    ->constrained('departments') // plural table name
                    ->onDelete('cascade');


            $table->string('client_name', 150);
            $table->text('client_address')->nullable();
            $table->date('job_order_date');
            $table->string('report_issue_to', 150);
            $table->string('reference_no', 50)->unique();
            $table->string('contact_no', 20);
            $table->string('contact_email', 150);
            

            $table->boolean('hold_status')->default(false);
            $table->string('upload_letter_path', 255)->nullable();

            // Polymorphic relation: either admin or user
            $table->unsignedBigInteger('created_by_id');
            $table->string('created_by_type');

            $table->timestamps();
            $table->softDeletes();

            // Foreign key: marketing_id references users.user_code (both must be string(255))
            $table->foreign('marketing_id')->references('user_code')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('new_bookings');
    }
}

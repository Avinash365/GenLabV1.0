<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE new_bookings 
            MODIFY payment_option 
            ENUM('bill', 'without_bill', 'old_bill') 
            DEFAULT 'without_bill'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE new_bookings 
            MODIFY payment_option 
            ENUM('bill', 'without_bill') 
            DEFAULT 'without_bill'
        ");
    }
};

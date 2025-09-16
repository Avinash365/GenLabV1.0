<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('gstin')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // Add client_id to existing bookings table
        Schema::table('new_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('new_bookings', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            }
        });
    }

    public function down(): void {
        Schema::table('new_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
        Schema::dropIfExists('clients');
    }
};

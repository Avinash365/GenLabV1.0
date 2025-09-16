<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_features', function (Blueprint $table) {
            $table->id();
            $table->boolean('backed_booking')->default(false); // for backdated booking
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_features');
    }
};

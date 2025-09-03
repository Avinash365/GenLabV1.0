<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('sender_guard', 32)->nullable()->after('original_name');
            $table->string('sender_name', 191)->nullable()->after('sender_guard');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['sender_guard', 'sender_name']);
        });
    }
};
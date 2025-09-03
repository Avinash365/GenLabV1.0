<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'reply_to_message_id')) {
                $table->unsignedBigInteger('reply_to_message_id')->nullable()->after('sender_name');
                $table->index('reply_to_message_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'reply_to_message_id')) {
                $table->dropIndex(['reply_to_message_id']);
                $table->dropColumn('reply_to_message_id');
            }
        });
    }
};
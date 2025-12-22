<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'sender_type')) {
                $table->string('sender_type')->nullable()->after('sender_id');
            }
            if (!Schema::hasColumn('messages', 'receiver_type')) {
                $table->string('receiver_type')->nullable()->after('receiver_id');
            }
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'sender_type')) {
                $table->dropColumn('sender_type');
            }
            if (Schema::hasColumn('messages', 'receiver_type')) {
                $table->dropColumn('receiver_type');
            }
        });
    }
};

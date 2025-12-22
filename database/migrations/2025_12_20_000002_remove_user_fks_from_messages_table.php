<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // drop foreign keys if they exist
            try {
                if (Schema::hasColumn('messages', 'sender_id')) {
                    $table->dropForeign(['sender_id']);
                }
            } catch (\Exception $e) {
                // ignore
            }

            try {
                if (Schema::hasColumn('messages', 'receiver_id')) {
                    $table->dropForeign(['receiver_id']);
                }
            } catch (\Exception $e) {
                // ignore
            }
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            // attempt to re-add FK to users if users table exists
            if (Schema::hasTable('users')) {
                try {
                    $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                } catch (\Exception $e) {
                    // ignore
                }
                try {
                    $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
                } catch (\Exception $e) {
                    // ignore
                }
            }
        });
    }
};

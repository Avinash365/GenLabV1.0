<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('user_id');

            // Optional: add foreign key if you have an admins table
            // $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            // Drop foreign key first if exists
            // $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};

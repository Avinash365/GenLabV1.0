<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add client_id column
            $table->unsignedBigInteger('client_id')->nullable()->after('id');
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Add marketing_user_code column
            $table->string('marketing_user_code', 255)->nullable()->after('client_id');
            $table->foreign('marketing_user_code')
                ->references('user_code')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['client_id']);
            $table->dropForeign(['marketing_user_code']);

            // Drop columns
            $table->dropColumn(['client_id', 'marketing_user_code']);
        });
    }
};

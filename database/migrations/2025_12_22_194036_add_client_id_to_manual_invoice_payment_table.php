<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('manual_invoice_payment', function (Blueprint $table) {

            // client_id (assuming clients.id)
            $table->unsignedBigInteger('client_id')
                  ->after('invoice_no')
                  ->index()
                  ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('manual_invoice_payment', function (Blueprint $table) {
            // Drop FK first if added
            // $table->dropForeign(['client_id']);

            $table->dropColumn('client_id');
        });
    }
};

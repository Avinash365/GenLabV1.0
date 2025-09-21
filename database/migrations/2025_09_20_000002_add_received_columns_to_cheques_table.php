<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->string('received_party_name')->nullable()->after('status');
            $table->date('received_cheque_date')->nullable()->after('received_party_name');
            $table->decimal('received_amount', 12, 2)->nullable()->after('received_cheque_date');
            $table->string('received_copy_path')->nullable()->after('received_amount');
            $table->text('received_note')->nullable()->after('received_copy_path');

            $table->date('deposit_date')->nullable()->after('received_note');
            $table->string('deposit_person')->nullable()->after('deposit_date');
            $table->boolean('deposit_status')->default(false)->after('deposit_person');
        });
    }

    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->dropColumn([
                'received_party_name',
                'received_cheque_date',
                'received_amount',
                'received_copy_path',
                'received_note',
                'deposit_date',
                'deposit_person',
                'deposit_status',
            ]);
        });
    }
};

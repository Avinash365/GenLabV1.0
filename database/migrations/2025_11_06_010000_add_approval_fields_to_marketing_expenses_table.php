<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->decimal('approved_amount', 12, 2)->default(0)->after('amount');
            $table->text('approval_note')->nullable()->after('description');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->dropColumn(['approved_amount','approval_note','approved_at']);
        });
    }
};

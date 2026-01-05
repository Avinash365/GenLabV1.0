<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cheque_templates', function (Blueprint $table) {
            $table->boolean('is_bold')->default(false)->after('letter_spacing');
        });
    }

    public function down(): void
    {
        Schema::table('cheque_templates', function (Blueprint $table) {
            $table->dropColumn('is_bold');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cheque_templates', function (Blueprint $table) {
            $table->float('letter_spacing')->nullable()->after('font_size');
        });
    }

    public function down(): void
    {
        Schema::table('cheque_templates', function (Blueprint $table) {
            $table->dropColumn('letter_spacing');
        });
    }
};

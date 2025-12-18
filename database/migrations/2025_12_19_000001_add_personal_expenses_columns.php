<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('personal_expenses')) {
            return;
        }

        Schema::table('personal_expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_expenses', 'approved_amount')) {
                $table->decimal('approved_amount', 12, 2)->nullable()->default(0)->after('amount');
            }
            if (! Schema::hasColumn('personal_expenses', 'submitted_for_approval')) {
                $table->boolean('submitted_for_approval')->default(false)->after('status');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('personal_expenses')) {
            return;
        }

        Schema::table('personal_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('personal_expenses', 'approved_amount')) {
                $table->dropColumn('approved_amount');
            }
            if (Schema::hasColumn('personal_expenses', 'submitted_for_approval')) {
                $table->dropColumn('submitted_for_approval');
            }
        });
    }
};

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
            if (! Schema::hasColumn('personal_expenses', 'from_date')) {
                $table->date('from_date')->nullable()->after('approved_amount');
            }
            if (! Schema::hasColumn('personal_expenses', 'to_date')) {
                $table->date('to_date')->nullable()->after('from_date');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('personal_expenses')) {
            return;
        }

        Schema::table('personal_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('personal_expenses', 'from_date')) {
                $table->dropColumn('from_date');
            }
            if (Schema::hasColumn('personal_expenses', 'to_date')) {
                $table->dropColumn('to_date');
            }
        });
    }
};

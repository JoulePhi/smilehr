<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_financials', function (Blueprint $table) {
            $table->dropColumn('insurance_by_employee');
            $table->dropColumn('bpjs_jht_percent');
            $table->dropColumn('bpjs_kesehatan_percent');
            $table->dropColumn('bpjs_jp_percent');

            $table->decimal('bpjs_jht_user_percent', 5, 2)->default(0)->after('daily_allowance');
            $table->decimal('bpjs_health_user_percent', 5, 2)->default(0)->after('bpjs_jht_user_percent');
            $table->decimal('bpjs_jp_user_percent', 5, 2)->default(0)->after('bpjs_health_user_percent');
            $table->decimal('others_user_percent', 5, 2)->default(0)->after('bpjs_jp_user_percent');
            $table->decimal('bpjs_jht_company_percent', 5, 2)->default(0)->after('others_user_percent');
            $table->decimal('bpjs_health_company_percent', 5, 2)->default(0)->after('bpjs_jht_company_percent');
            $table->decimal('bpjs_jp_company_percent', 5, 2)->default(0)->after('bpjs_health_company_percent');
            $table->decimal('others_company_percent', 5, 2)->default(0)->after('bpjs_jp_company_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_financials', function (Blueprint $table) {
            $table->decimal('insurance_by_employee', 5, 2)->default(0)->after('daily_allowance');
            $table->decimal('bpjs_jht_percent', 5, 2)->default(0)->after('daily_allowance');
            $table->decimal('bpjs_kesehatan_percent', 5, 2)->default(0)->after('bpjs_jht_percent');
            $table->decimal('bpjs_jp_percent', 5, 2)->default(0)->after('bpjs_kesehatan_percent');

            $table->dropColumn('bpjs_jht_user_percent');
            $table->dropColumn('bpjs_health_user_percent');
            $table->dropColumn('bpjs_jp_user_percent');
            $table->dropColumn('others_user_percent');
            $table->dropColumn('bpjs_jht_company_percent');
            $table->dropColumn('bpjs_health_company_percent');
            $table->dropColumn('bpjs_jp_company_percent');
            $table->dropColumn('others_company_percent');
        });
    }
};

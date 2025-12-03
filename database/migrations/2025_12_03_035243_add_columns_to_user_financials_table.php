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
            $table->decimal('total_salary')->nullable()->after('basic_salary');
            $table->string('hourly_wages_based_on')->nullable()->after('daily_allowance');
            $table->boolean('overtime_need_approval')->default(false)->after('hourly_wages_based_on');
            $table->string('overtime_calculation_method')->nullable()->after('overtime_need_approval');
            $table->string('weekday_pattern')->nullable()->after('overtime_calculation_method');
            $table->decimal('overtime_multiplier')->nullable()->after('weekday_pattern');
            $table->decimal('special_overtime_multiplier')->nullable()->after('overtime_multiplier');

            $table->string('hourly_deduction_based_on')->nullable()->after('special_overtime_multiplier');
            $table->decimal('daily_late_deduction_amount')->nullable()->after('hourly_deduction_based_on');
            $table->decimal('daily_overtime_incentive')->nullable()->after('daily_late_deduction_amount');
            $table->decimal('daily_leave_balance_incentive')->nullable()->after('daily_overtime_incentive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_financials', function (Blueprint $table) {
            $table->dropColumn([
                'hourly_wages_based_on',
                'overtime_need_approval',
                'overtime_calculation_method',
                'weekday_pattern',
                'overtime_multiplier',
                'special_overtime_multiplier',
                'hourly_deduction_based_on',
                'daily_late_deduction_amount',
                'daily_overtime_incentive',
                'daily_leave_balance_incentive',
            ]);
        });
    }
};

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
        Schema::create('user_financials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('npwp')->nullable();
            $table->string('tax_type')->nullable();
            $table->decimal('tax_deduction_amount', 15, 2)->default(0);
            $table->decimal('tax_allowance_percent', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bpjs_tk_number')->nullable();
            $table->string('bpjs_kes_number')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('routine_dues', 15, 2)->default(0);
            $table->decimal('fixed_allowance', 15, 2)->default(0);
            $table->decimal('other_allowance', 15, 2)->default(0);
            $table->decimal('daily_allowance', 15, 2)->default(0);
            $table->decimal('insurance_by_employee', 15, 2)->default(0);
            $table->decimal('bpjs_jht_percent', 15, 2)->default(0);
            $table->decimal('bpjs_kesehatan_percent', 15, 2)->default(0);
            $table->decimal('bpjs_jp_percent', 15, 2)->default(0);
            $table->decimal('bpjs_jkm_percent', 15, 2)->default(0);
            $table->decimal('bpjs_jkk_percent', 15, 2)->default(0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_financials');
    }
};

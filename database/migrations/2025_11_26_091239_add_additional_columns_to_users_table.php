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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('id')->nullable();
            $table->unsignedBigInteger('branch_office_id')->after('company_id')->nullable();
            $table->unsignedBigInteger('department_id')->after('branch_office_id')->nullable();
            $table->unsignedBigInteger('position_id')->after('department_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->after('position_id')->nullable();
            $table->string('nik')->after('cost_center_id')->unique()->nullable();
            $table->string('profile_url')->after('name')->nullable();
            $table->string('phone')->after('email')->nullable();
            $table->date('birth_date')->after('phone')->nullable();
            $table->string('birth_place')->after('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->after('birth_place')->nullable();
            $table->string('religion')->after('gender')->nullable();
            $table->string('phone_number')->after('religion')->nullable();
            $table->text('address')->after('phone_number')->nullable();
            $table->text('domicile_address')->after('address')->nullable();
            $table->string('last_education')->after('domicile_address')->nullable();
            $table->date('join_date')->after('last_education')->nullable();
            $table->enum('employment_status', ['permanent', 'contract', 'other'])->after('join_date')->nullable();
            $table->date('contract_start')->after('employment_status')->nullable();
            $table->date('contract_end')->after('contract_start')->nullable();

            $table->decimal('late_deduction', 15, 2)->after('contract_end')->default(0);
            $table->integer('late_tolerance')->after('late_deduction')->default(0);
            $table->boolean('allow_remote_attendance')->after('late_tolerance')->default(false);
            $table->boolean('allow_branch_hopping')->after('allow_remote_attendance')->default(false);

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('branch_office_id')->references('id')->on('branch_offices')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_office_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn([
                'branch_id',
                'department_id',
                'position_id',
                'cost_center_id',
                'nik',
                'profile_url',
                'phone',
                'birth_date',
                'birth_place',
                'gender',
                'religion',
                'phone_number',
                'address',
                'domicile_address',
                'last_education',
                'join_date',
                'employment_status',
                'contract_start',
                'contract_end',
                'late_deduction',
                'late_tolerance',
                'allow_remote_attendance',
                'allow_branch_hopping',
            ]);
        });
    }
};

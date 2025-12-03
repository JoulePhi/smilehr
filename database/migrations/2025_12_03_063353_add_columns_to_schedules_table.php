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
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('scheduled_date');
            $table->date('shift_in_date')->after('user_id');
            $table->date('shift_out_date')->after('shift_in_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->date('scheduled_date')->after('user_id');
            $table->dropColumn('shift_in_date');
            $table->dropColumn('shift_out_date');
        });
    }
};

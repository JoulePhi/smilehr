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
        $defaultPin = bcrypt('000000');
        Schema::table('users', function (Blueprint $table) use ($defaultPin) {
            $table->string('pin')->default($defaultPin)->after('password');
            $table->json('face_descriptor')->nullable()->after('pin');
            $table->string('employee_id_code')->unique()->nullable()->after('face_descriptor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pin');
            $table->dropColumn('face_descriptor');
            $table->dropColumn('employee_id_code');
        });
    }
};

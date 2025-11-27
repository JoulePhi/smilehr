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
            $table->string('id_card_number', 50)->nullable()->after('last_education');
            $table->enum('check_in_mode', ['card_and_photo', 'card_only'])->default('card_and_photo')->after('allow_branch_hopping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_card_number');
            $table->dropColumn('check_in_mode');
        });
    }
};

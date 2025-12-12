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
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['location_lat', 'location_lng', 'date']);
            $table->string('lat_in')->nullable()->after('time_in');
            $table->string('lng_in')->nullable()->after('lat_in');
            $table->string('lat_out')->nullable()->after('time_out');
            $table->string('lng_out')->nullable()->after('lat_out');
            $table->date('date_in')->nullable()->after('user_id');
            $table->date('date_out')->nullable()->after('date_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['lat_in', 'lng_in', 'lat_out', 'lng_out', 'date_in', 'date_out']);
            $table->string('location_lat')->nullable()->after('time_in');
            $table->string('location_lng')->nullable()->after('location_lat');
            $table->date('date')->nullable()->after('location_lng');
        });
    }
};

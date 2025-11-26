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
        Schema::create('branch_offices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->text('address');
            $table->string('npwp');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->unsignedInteger('radius');
            $table->string('phone_number')->nullable();
            $table->time('work_hour_in_weekday');
            $table->time('work_hour_out_weekday');
            $table->time('work_hour_in_weekend')->nullable();
            $table->time('work_hour_out_weekend')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_offices');
    }
};

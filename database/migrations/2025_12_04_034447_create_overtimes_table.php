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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('shift_in_date');
            $table->date('shift_out_date');
            $table->time('shift_in');
            $table->time('shift_out');
            $table->text('remarks');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_validated')->default(false);
            $table->boolean('is_special')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};

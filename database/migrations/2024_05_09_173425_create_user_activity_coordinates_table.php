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
        Schema::create('user_activity_coordinates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user_activity');
            $table->text('coordinate');
            $table->timestamps();

            $table->foreign('id_user_activity')->references('id')->on('user_activities')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_coordinates');
    }
};

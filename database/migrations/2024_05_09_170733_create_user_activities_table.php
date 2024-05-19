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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_activity');
            $table->time('start_time');
            $table->time('finishing_time');
            $table->boolean('done')->default(false);
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('id_activity')->references('id')->on('activities')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};

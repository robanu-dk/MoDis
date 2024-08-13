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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->String('name');
            $table->date('date');
            $table->time('start_time', 0);
            $table->time('end_time', 0);
            $table->text('note')->nullable();
            $table->boolean('created_by_guide')->default(0);
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->String('name', 255);
            $table->String('poster', 50)->nullable();
            $table->enum('type', ['Offline', 'Online', 'Hybrid']);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('location')->nullable();
            $table->text('coordinate_location')->nullable();
            $table->String('contact_person', 13);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

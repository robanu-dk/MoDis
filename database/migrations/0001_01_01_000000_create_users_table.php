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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('username', 255);
            $table->string('email', 255)->unique();
            $table->boolean('role');
            $table->boolean('jenis_kelamin');
            $table->string('password', 255);
            $table->string('profile_image', 255)->nullable();
            $table->unsignedBigInteger('id_pendamping')->nullable();
            $table->string('reset_password_token', 255)->nullable();
            $table->string('token', 12)->nullable();
            $table->boolean('verified')->default(1);
            $table->timestamps();

            $table->foreign('id_pendamping')->references('id')->on('users')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

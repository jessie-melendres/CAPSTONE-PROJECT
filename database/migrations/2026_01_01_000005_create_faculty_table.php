<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty', function (Blueprint $table) {
            $table->string('faculty_id', 20)->primary();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('dept_id', 20);
            $table->string('status', 20)->default('Teaching');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('dept_id')->references('dept_id')->on('departments')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty');
    }
};

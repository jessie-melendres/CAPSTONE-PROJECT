<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('student_id', 20)->primary();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email_address', 100)->unique()->nullable();
            $table->string('program_id', 20);
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedTinyInteger('year_level')->default(1);
            $table->string('status', 20)->default('Active');
            $table->timestamps();

            $table->foreign('program_id')->references('program_id')->on('programs')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

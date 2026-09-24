<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id('enrollment_id');
            $table->string('semester', 20);
            $table->string('school_year', 20);
            $table->string('student_id', 20);
            $table->unsignedBigInteger('schedule_id');
            $table->string('status', 20)->default('Enrolled');
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('schedule_id')->references('schedule_id')->on('schedules')->cascadeOnUpdate()->restrictOnDelete();
            $table->unique(['student_id', 'schedule_id', 'semester', 'school_year'], 'enrollment_unique_term');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->string('room_assignment', 50);
            $table->string('day', 15);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('faculty_id', 20);
            $table->string('subject_id', 20);
            $table->timestamps();

            $table->foreign('faculty_id')->references('faculty_id')->on('faculty')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('subject_id')->references('subject_id')->on('subjects')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

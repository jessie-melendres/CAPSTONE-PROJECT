<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->string('program_id', 20)->primary();
            $table->string('program_name', 100);
            $table->string('dept_id', 20);
            $table->timestamps();

            $table->foreign('dept_id')->references('dept_id')->on('departments')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};

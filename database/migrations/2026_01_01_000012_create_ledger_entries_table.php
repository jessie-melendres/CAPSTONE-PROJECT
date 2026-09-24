<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id('ledger_entry_id');
            $table->string('student_id', 20);
            $table->string('description', 150);
            $table->string('entry_type', 20);
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('recorded_by_user_id');
            $table->date('entry_date');
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('recorded_by_user_id')->references('user_id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};

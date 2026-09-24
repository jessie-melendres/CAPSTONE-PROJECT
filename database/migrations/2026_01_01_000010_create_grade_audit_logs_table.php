<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_audit_logs', function (Blueprint $table) {
            $table->id('audit_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->string('field_changed', 30);
            $table->string('old_value', 20)->nullable();
            $table->string('new_value', 20)->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->foreign('grade_id')->references('grade_id')->on('grades')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('user_id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_audit_logs');
    }
};

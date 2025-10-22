<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_status_trails', function (Blueprint $table) {
            $table->id('status_trail_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->string('status_name'); // e.g. active, first_warning, second_warning, request_meeting, graduate_student, suspended, terminated
            $table->dateTime('date')->nullable();
            $table->text('comment')->nullable();
            $table->string('changed_by')->nullable(); // Admin who made the change
            $table->timestamps();

            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_status_trails');
    }
};


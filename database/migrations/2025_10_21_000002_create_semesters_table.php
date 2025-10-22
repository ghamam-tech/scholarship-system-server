<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id('semester_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->integer('semester_no'); // Semester number (1, 2, 3, etc.)
            $table->integer('courses')->default(0); // Number of courses
            $table->integer('credits')->default(0); // Total credits
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('cgpa', 4, 2)->nullable(); // CGPA for this semester
            $table->string('status', 50)->default('active'); // active, completed, failed, withdrawn
            $table->string('transcript')->nullable(); // File path to transcript
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();

            $table->unique(['student_id', 'semester_no']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};


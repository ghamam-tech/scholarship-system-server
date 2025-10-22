<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');

            // FKs
            $table->foreignId('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->foreignId('applicant_id')
                ->references('applicant_id')->on('applicants')
                ->cascadeOnDelete();

            $table->foreignId('approved_application_id')
                ->references('approved_application_id')->on('approved_applicant_applications')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

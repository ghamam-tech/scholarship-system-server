<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('applicant_applications', function (Blueprint $table) {
            $table->id('application_id');

            // Three specialization choices (plain text)
            $table->string('specialization_1')->nullable();
            $table->string('specialization_2')->nullable();
            $table->string('specialization_3')->nullable();

            // University & Country (plain text, NOT FK)
            $table->string('university_name')->nullable();
            $table->string('country_name')->nullable();

            $table->decimal('tuition_fee', 12, 2)->nullable();
            $table->boolean('has_active_program')->default(false);
            $table->unsignedTinyInteger('current_semester_number')->nullable();

            $table->decimal('cgpa', 4, 2)->nullable();
            $table->decimal('cgpa_out_of', 4, 2)->nullable();

            $table->boolean('terms_and_condition')->default(false);
            $table->string('offer_letter_file')->nullable(); // S3 key

            $table->foreignId('applicant_id')
                  ->constrained('applicants', 'applicant_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // up to three target scholarships (keep as FK)
            $table->foreignId('scholarship_id_1')->nullable()
                  ->constrained('scholarships', 'scholarship_id')
                  ->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('scholarship_id_2')->nullable()
                  ->constrained('scholarships', 'scholarship_id')
                  ->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('scholarship_id_3')->nullable()
                  ->constrained('scholarships', 'scholarship_id')
                  ->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('applicant_applications');
    }
};

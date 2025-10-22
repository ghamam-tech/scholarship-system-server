<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->foreignId('user_id')->unique()->constrained('users','user_id')->cascadeOnDelete();
            $table->foreignId('approved_application_id')->nullable()
                ->constrained('approved_applicant_applications','approved_application_id')->nullOnDelete();

            // profile fields copied from applicants (nullable)
            $table->string('ar_name')->nullable();
            $table->string('en_name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('passport_number')->nullable();
            $table->string('parent_contact_name')->nullable();
            $table->string('parent_contact_phone', 30)->nullable();
            $table->string('residence_country')->nullable();
            $table->string('language', 50)->nullable();
            $table->boolean('is_studied_in_saudi')->default(false);
            $table->string('passport_copy_img')->nullable();
            $table->string('personal_image')->nullable();
            $table->string('volunteering_certificate_file')->nullable();
            $table->string('tahsili_file')->nullable();
            $table->string('qudorat_file')->nullable();
            $table->decimal('tahseeli_percentage',5,2)->nullable();
            $table->decimal('qudorat_percentage',5,2)->nullable();

            // lifecycle
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamp('graduated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

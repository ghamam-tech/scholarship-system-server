<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary Key
            $table->id('student_id');

            // Personal Details
            $table->string('ar_name')->nullable();
            $table->string('en_name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('passport_number')->nullable();
            $table->date('date_of_birth')->nullable();

            // Parent Contact
            $table->string('parent_contact_name')->nullable();
            $table->string('parent_contact_phone', 20)->nullable();

            // Residence
            $table->string('residence_country')->nullable();

            // Document URLs (S3)
            $table->string('passport_copy_img')->nullable();
            $table->string('volunteering_certificate_file')->nullable();
            $table->string('language')->nullable();

            // Education
            $table->boolean('is_studied_in_saudi')->default(false);
            $table->string('tahsili_file')->nullable();
            $table->string('qudorat_file')->nullable();
            $table->decimal('tahseeli_percentage', 5, 2)->nullable();
            $table->decimal('qudorat_percentage', 5, 2)->nullable();

            // Foreign Key to users table (NO application_id)
            $table->foreignId('user_id')->unique()->constrained('users', 'user_id')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('students');
    }
};


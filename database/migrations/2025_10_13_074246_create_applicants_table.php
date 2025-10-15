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
        Schema::create('applicants', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary Key
            $table->id('applicant_id');

            // Personal Details (nullable for registration)
            $table->string('ar_name')->nullable();
            $table->string('en_name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('passport_number')->nullable();
            $table->date('date_of_birth')->nullable();

            // Parent Contact (nullable)
            $table->string('parent_contact_name')->nullable();
            $table->string('parent_contact_phone', 20)->nullable();

            // Residence (nullable)
            $table->string('residence_country')->nullable();

            // Document URLs (S3) - already nullable
            $table->string('passport_copy_img')->nullable();
            $table->string('volunteering_certificate_file')->nullable();

            // Language (nullable)
            $table->string('language')->nullable();

            // Education
            $table->boolean('is_studied_in_saudi')->default(false);
            $table->string('tahsili_file')->nullable(); // S3 URL
            $table->string('qudorat_file')->nullable(); // S3 URL

            // Foreign Key to users table
            $table->foreignId('user_id')->unique()->constrained('users', 'user_id')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('applicants');
    }
};

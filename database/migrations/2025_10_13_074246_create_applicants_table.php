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

            // Primary key
            $table->id('applicant_id');

            // Names
            $table->string('ar_name');
            $table->string('en_name');

            // Personal info
            $table->string('nationality')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('phone', 20)->nullable();

            // Passport
            $table->string('passport_number')->nullable();
            $table->date('date_of_birth')->nullable();

            // Parent contact
            $table->string('parent_contact_name')->nullable();
            $table->string('parent_contact_phone', 20)->nullable();

            // Residence and documents
            $table->string('residence_country')->nullable();
            $table->string('passport_copy_url')->nullable();
            $table->string('volunteering_certificate_url')->nullable();

            // Language and study info
            $table->string('language', 50)->nullable();
            $table->boolean('is_studied_in_saudi')->default(false);

            // Foreign key to users table
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')
                  ->references('user_id') // your users table PK
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

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

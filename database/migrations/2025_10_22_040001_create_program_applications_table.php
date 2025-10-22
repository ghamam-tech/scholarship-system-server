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
        // Check if table exists, if so update it, otherwise create it
        if (Schema::hasTable('program_applications')) {
            // Update existing table
            Schema::table('program_applications', function (Blueprint $table) {
                // Update the enum to include all required values
                $table->enum('application_status', ['invite', 'accepted', 'excuse', 'approved_excuse', 'doesn_attend', 'attend', 'approved', 'rejected', 'completed', 'doesnt_respond'])->default('invite')->change();

                // Add excuse fields if they don't exist
                if (!Schema::hasColumn('program_applications', 'excuse_reason')) {
                    $table->text('excuse_reason')->nullable();
                }
                if (!Schema::hasColumn('program_applications', 'excuse_file')) {
                    $table->string('excuse_file')->nullable();
                }
            });
        } else {
            // Create new table
            Schema::create('program_applications', function (Blueprint $table) {
                $table->engine = 'InnoDB';

                // Primary Key
                $table->id('application_program_id');

                // Application Details
                $table->enum('application_status', ['invite', 'accepted', 'excuse', 'approved_excuse', 'doesn_attend', 'attend', 'approved', 'rejected', 'completed', 'doesnt_respond'])->default('invite');
                $table->string('certificate_token')->nullable();
                $table->text('comment')->nullable();

                // Excuse fields for student rejection
                $table->text('excuse_reason')->nullable();
                $table->string('excuse_file')->nullable();

                // Foreign Keys
                $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
                $table->foreignId('program_id')->constrained('programs', 'program_id')->onDelete('cascade');

                $table->timestamps();

                // Ensure a student can only apply once per program
                $table->unique(['student_id', 'program_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_applications', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['program_id']);
        });
        Schema::dropIfExists('program_applications');
    }
};

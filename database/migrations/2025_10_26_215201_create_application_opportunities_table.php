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
        if (Schema::hasTable('application_opportunities')) {
            // Update existing table
            Schema::table('application_opportunities', function (Blueprint $table) {
                // Update the enum to include all required values
                $table->enum('application_status', ['invite', 'accepted', 'excuse', 'approved_excuse', 'rejected_excuse', 'doesn_attend', 'attend', 'approved', 'rejected', 'completed', 'doesnt_respond'])->default('invite')->change();

                // Add excuse fields if they don't exist
                if (!Schema::hasColumn('application_opportunities', 'excuse_reason')) {
                    $table->text('excuse_reason')->nullable();
                }
                if (!Schema::hasColumn('application_opportunities', 'excuse_file')) {
                    $table->string('excuse_file')->nullable();
                }
            });
        } else {
            // Create new table
            Schema::create('application_opportunities', function (Blueprint $table) {
                $table->engine = 'InnoDB';

                // Primary Key
                $table->id('application_opportunity_id');

                // Application Details
                $table->enum('application_status', ['invite', 'accepted', 'excuse', 'approved_excuse', 'rejected_excuse', 'doesn_attend', 'attend', 'approved', 'rejected', 'completed', 'doesnt_respond'])->default('invite');
                $table->string('certificate_token')->nullable();
                $table->text('comment')->nullable();

                // Excuse fields for student rejection
                $table->text('excuse_reason')->nullable();
                $table->string('excuse_file')->nullable();

                // Additional fields for opportunities
                $table->string('attendece_mark')->nullable(); // Note: keeping original spelling as in ERD

                // Foreign Keys
                $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
                $table->foreignId('opportunity_id')->constrained('opportunities', 'opportunity_id')->onDelete('cascade');

                $table->timestamps();

                // Ensure a student can only apply once per opportunity
                $table->unique(['student_id', 'opportunity_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_opportunities', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['opportunity_id']);
        });
        Schema::dropIfExists('application_opportunities');
    }
};

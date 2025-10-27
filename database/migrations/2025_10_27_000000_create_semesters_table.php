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
        Schema::create('semesters', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary Key
            $table->id('semester_id');

            // Semester Details
            $table->decimal('credit_hours', 5, 2)->nullable();
            $table->unsignedSmallInteger('total_subjects')->nullable();
            $table->string('status', 50)->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->decimal('cgpa_out_of', 4, 2)->nullable();
            $table->unsignedTinyInteger('semester_number')->nullable();
            $table->date('starting_date')->nullable();
            $table->date('ending_date')->nullable();
            $table->string('transcript_path')->nullable();

            // Relationships
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};

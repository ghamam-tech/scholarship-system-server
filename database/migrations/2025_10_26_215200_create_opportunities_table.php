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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary Key
            $table->id('opportunity_id');

            // Opportunity Details
            $table->string('title');
            $table->text('discription')->nullable(); // Note: keeping original spelling as in ERD
            $table->date('date')->nullable();
            $table->string('location')->nullable();
            $table->string('country')->nullable();
            $table->string('category')->nullable();
            $table->string('image_file')->nullable();
            $table->string('qr_url')->nullable();

            // Opportunity Coordinator Details
            $table->string('opportunity_coordinatior_name')->nullable(); // Note: keeping original spelling as in ERD
            $table->string('opportunity_coordinatior_phone')->nullable(); // Note: keeping original spelling as in ERD
            $table->string('opportunity_coordinatior_email')->nullable();

            // Opportunity Features - Default to true as requested
            $table->boolean('enable_qr_attendance')->default(true);
            $table->boolean('generate_certificates')->default(true);

            // Opportunity Status and Dates
            $table->enum('opportunity_status', ['active', 'inactive', 'completed', 'cancelled'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Additional fields for opportunities
            $table->string('volunteer_role')->nullable();
            $table->integer('volunteering_hours')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};


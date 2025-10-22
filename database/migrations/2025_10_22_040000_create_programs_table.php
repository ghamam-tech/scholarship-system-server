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
        Schema::create('programs', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary Key
            $table->id('program_id');

            // Program Details
            $table->string('title');
            $table->text('discription')->nullable(); // Note: keeping original spelling as in ERD
            $table->date('date')->nullable();
            $table->string('location')->nullable();
            $table->string('country')->nullable();
            $table->string('category')->nullable();
            $table->string('image_file')->nullable();
            $table->string('qr_url')->nullable();

            // Program Coordinator Details
            $table->string('program_coordinatior_name')->nullable(); // Note: keeping original spelling as in ERD
            $table->string('program_coordinatior_phone')->nullable(); // Note: keeping original spelling as in ERD
            $table->string('program_coordinatior_email')->nullable();

            // Program Features
            $table->boolean('enable_qr_attendance')->default(false);
            $table->boolean('generate_certificates')->default(false);

            // Program Status and Dates
            $table->enum('program_status', ['active', 'inactive', 'completed', 'cancelled'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};

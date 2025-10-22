<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approved_applicant_applications', function (Blueprint $table) {
            $table->id('approved_application_id');
            $table->foreignId('application_id')->unique()->constrained('applicant_applications','application_id')->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained('scholarships','scholarship_id')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users','user_id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approved_applicant_applications');
    }
};

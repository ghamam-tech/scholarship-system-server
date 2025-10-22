<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approved_applicant_applications', function (Blueprint $table) {
            $table->id('approved_application_id');
            $table->json('benefits');
            $table->boolean('has_accepted_scholarship')->default(false);

            // FKs
            $table->foreignId('scholarship_id')
                ->constrained('scholarships', 'scholarship_id')
                ->cascadeOnDelete();

            $table->foreignId('application_id')
                ->references('application_id')->on('applicant_applications')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approved_applicant_applications');
    }
};

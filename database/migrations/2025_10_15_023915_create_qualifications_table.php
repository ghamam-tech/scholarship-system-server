<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id('qualification_id');

            $table->enum('qualification_type', [
                'high_school',
                'diploma',
                'bachelor',
                'master',
                'phd',
                'other'
            ])->nullable();

            $table->string('institute_name')->nullable();
            $table->year('year_of_graduation')->nullable();

            $table->decimal('cgpa', 4, 2)->nullable();
            $table->decimal('cgpa_out_of', 4, 2)->nullable();

            $table->string('language_of_study')->nullable();
            $table->string('specialization')->nullable();
            $table->string('research_title')->nullable();
            $table->string('document_file')->nullable();

            // Link to user
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};

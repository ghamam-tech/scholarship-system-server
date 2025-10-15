<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_scholarship', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                  ->constrained('countries', 'country_id')
                  ->cascadeOnDelete();
            $table->foreignId('scholarship_id')
                  ->constrained('scholarships', 'scholarship_id')
                  ->cascadeOnDelete();
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['country_id', 'scholarship_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_scholarship');
    }
};
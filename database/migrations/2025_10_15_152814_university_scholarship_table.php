<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('university_scholarship', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')
                  ->constrained('scholarships', 'scholarship_id')
                  ->cascadeOnDelete();
            $table->foreignId('university_id')
                  ->constrained('universities', 'university_id')
                  ->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['scholarship_id', 'university_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('university_scholarship');
    }
};
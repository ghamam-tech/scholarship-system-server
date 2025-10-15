// database/migrations/2025_01_01_000020_create_scholarships_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id('scholarship_id');
            $table->string('scholarship_name');
            $table->string('scholarship_type')->nullable();   // fixed typo
            $table->string('allowed_program')->nullable();
            $table->unsignedInteger('total_beneficiaries')->nullable();
            $table->date('opening_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hided')->default(false);

            // FK to sponsors (adjust PK name if your sponsors table uses a different key)
            $table->foreignId('sponsor_id')
                ->constrained('sponsors', 'sponsor_id')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};

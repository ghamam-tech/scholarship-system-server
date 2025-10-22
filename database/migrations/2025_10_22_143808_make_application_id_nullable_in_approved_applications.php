<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('approved_applicant_applications', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['application_id']);

            // Drop the column
            $table->dropColumn('application_id');
        });

        Schema::table('approved_applicant_applications', function (Blueprint $table) {
            // Re-add the column as nullable
            $table->foreignId('application_id')
                ->nullable()
                ->constrained('applicant_applications', 'application_id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approved_applicant_applications', function (Blueprint $table) {
            // Drop modified column
            $table->dropForeign(['application_id']);
            $table->dropColumn('application_id');
        });

        Schema::table('approved_applicant_applications', function (Blueprint $table) {
            // Re-add as non-nullable (original state)
            $table->foreignId('application_id')
                ->constrained('applicant_applications', 'application_id')
                ->cascadeOnDelete();
        });
    }
};

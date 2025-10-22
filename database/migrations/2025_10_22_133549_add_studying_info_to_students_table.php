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
        Schema::table('students', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('student_id');
            $table->string('offer_letter')->nullable()->after('specialization');

            // FKs
            $table->foreignId('country_id')->nullable()
                ->references('country_id')->on('countries')
                ->cascadeOnDelete();
            $table->foreignId('university_id')->nullable()
                ->references('university_id')->on('universities')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['university_id']);
            $table->dropColumn(['specialization', 'offer_letter', 'country_id', 'university_id']);
        });
    }
};

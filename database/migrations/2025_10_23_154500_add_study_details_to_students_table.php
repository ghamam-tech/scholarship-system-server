<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('language_of_study')->nullable()->after('offer_letter');
            $table->decimal('yearly_tuition_fees', 12, 2)->nullable()->after('language_of_study');
            $table->string('study_period')->nullable()->after('yearly_tuition_fees');
            $table->unsignedTinyInteger('total_semesters_number')->nullable()->after('study_period');
            $table->unsignedTinyInteger('current_semester_number')->nullable()->after('total_semesters_number');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'language_of_study',
                'yearly_tuition_fees',
                'study_period',
                'total_semesters_number',
                'current_semester_number',
            ]);
        });
    }
};

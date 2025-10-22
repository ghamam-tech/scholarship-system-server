<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamp('migrated_to_student_at')->nullable();
            $table->timestamp('reactivated_from_student_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['is_archived', 'migrated_to_student_at', 'reactivated_from_student_at']);
        });
    }
};

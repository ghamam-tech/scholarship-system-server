<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applicant_application_statuses', function (Blueprint $table) {
            $table->id('applicationStatus_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnUpdate()->cascadeOnDelete();

            // Keep string (flexible); switch to enum if you have a fixed set.
            $table->string('status_name'); // e.g. submitted, first_approval, meeting, second_approval, final_approval, rejected
            $table->dateTime('date')->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_application_statuses');
    }
};

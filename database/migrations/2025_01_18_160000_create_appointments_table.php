<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary Key
            $table->id('appointment_id');

            // Always store in UTC
            $table->dateTime('starts_at_utc');
            $table->dateTime('ends_at_utc');

            // Metadata for generation/formatting
            $table->string('owner_timezone', 64);             // e.g. "Asia/Kuala_Lumpur"
            $table->unsignedSmallInteger('duration_min');     // e.g. 15/30/45

            // Optional meeting link (per appointment)
            $table->string('meeting_url', 2048)->nullable();

            // Booking status + who booked it (nullable until booked)
            $table->enum('status', ['available', 'booked', 'canceled'])->default('available')->index();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();

            // Audit
            $table->timestamp('booked_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();

            // Prevent duplicate time slots
            $table->unique('starts_at_utc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('appointments');
    }
};

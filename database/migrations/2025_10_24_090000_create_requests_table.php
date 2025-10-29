<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('request_type');
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('body');
            $table->string('current_status');
            $table->string('document_path')->nullable();

            $table->foreignId('student_id')
                ->references('student_id')->on('students')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};

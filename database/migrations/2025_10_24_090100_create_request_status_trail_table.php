<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('request_status_trail', function (Blueprint $table) {
            $table->id('request_status_id');
            $table->string('status');
            $table->text('comment')->nullable();
            $table->timestamp('date');
            $table->string('document_path')->nullable();

            $table->foreignId('request_id')
                ->references('request_id')->on('requests')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_status_trail');
    }
};

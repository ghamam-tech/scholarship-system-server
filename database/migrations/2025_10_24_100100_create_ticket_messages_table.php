<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->text('content');

            $table->foreignId('ticket_id')
                ->references('ticket_id')->on('tickets')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};

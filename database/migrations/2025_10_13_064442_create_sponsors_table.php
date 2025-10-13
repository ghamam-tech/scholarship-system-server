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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id('sponsor_id');
            $table->string('name');
            $table->string('country');
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->references('user_id') // your users table PK
                ->on('users')->cascadeOnDelete()->cascadeOnUpdate()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};

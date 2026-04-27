<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();

            // Usuário com quem a agenda foi compartilhada
            $table->foreignId('shared_with_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->enum('permission', ['view', 'edit'])->default('view');
            $table->timestamps();

            $table->unique(['schedule_id', 'shared_with_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_shares');
    }
};

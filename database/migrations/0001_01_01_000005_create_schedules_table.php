<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();

            // Horários de funcionamento por dia da semana (JSON):
            // {"mon":{"start":"09:00","end":"18:00","active":true}, "tue":{...}, ...}
            $table->json('working_hours')->nullable();

            // Duração padrão de cada slot em minutos
            $table->unsignedSmallInteger('slot_duration')->default(60);

            // Permite agendamento público (via link ou widget)
            $table->boolean('is_public')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

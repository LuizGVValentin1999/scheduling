<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_booking_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();

            // Token único que identifica o link público
            $table->string('token', 64)->unique();

            $table->string('label')->default('Agendamento');

            // Customizações do widget: cores, branding
            // {"primary_color":"#1976d2","accent_color":"#dc004e","title":"Agende aqui"}
            $table->json('settings')->nullable();

            $table->boolean('is_active')->default(true);
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_booking_links');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Armazena tokens OAuth dos usuários para Google Calendar / Outlook.
// Os tokens são criptografados via cast EncryptedCast no model.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['google', 'outlook']);

            // Tokens criptografados no banco
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->dateTime('token_expires_at')->nullable();

            // ID da agenda específica do provedor a sincronizar
            $table->string('calendar_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_integrations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // slug único usado em URLs amigáveis e no widget
            $table->string('slug')->unique();
            $table->enum('plan', ['free', 'pro', 'enterprise'])->default('free');
            // configurações gerais: fuso horário, idioma, etc.
            $table->json('settings')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

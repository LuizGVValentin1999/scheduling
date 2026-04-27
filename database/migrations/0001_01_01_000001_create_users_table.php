<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();

            // Senha nullable — usuários OAuth não têm senha local
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            // OAuth: provider = 'google' | 'microsoft' | null (cadastro manual)
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->index(['provider', 'provider_id']);

            // Tenant ativo na sessão atual do usuário
            $table->foreignId('current_tenant_id')
                  ->nullable()
                  ->constrained('tenants')
                  ->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};

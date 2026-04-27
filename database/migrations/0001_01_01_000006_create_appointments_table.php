<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();

            // Cliente pode ser do cadastro interno ou avulso (walk-in / booking público)
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedSmallInteger('duration_minutes');

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');

            // IDs de eventos externos para sincronização bidirecional
            $table->string('google_event_id')->nullable()->index();
            $table->string('outlook_event_id')->nullable()->index();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['schedule_id', 'starts_at']);
            $table->index(['tenant_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\CalendarSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job assíncrono para sincronizar agendamentos com Google Calendar e Outlook.
 * Usa a fila 'calendars' para não bloquear a fila principal.
 * QUEUE_CONNECTION=database no .env para persistência simples.
 */
class SyncCalendarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60; // segundos entre tentativas

    public function __construct(
        private readonly Appointment $appointment,
        private readonly string $action // create | update | delete
    ) {
        $this->onQueue('calendars');
    }

    public function handle(CalendarSyncService $syncService): void
    {
        $syncService->syncAppointment($this->appointment, $this->action);
    }
}

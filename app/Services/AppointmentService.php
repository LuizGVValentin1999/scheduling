<?php

namespace App\Services;

use App\Jobs\SyncCalendarJob;
use App\Models\Appointment;
use App\Models\Schedule;
use Illuminate\Support\Collection;

class AppointmentService
{
    /**
     * Lista agendamentos de uma agenda com filtro por período.
     * O TenantScope garante isolamento automático.
     */
    public function listForSchedule(Schedule $schedule, string $start, string $end): Collection
    {
        return $schedule->appointments()
            ->with('client')
            ->inRange($start, $end)
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Cria agendamento e dispara sincronização assíncrona com Google/Outlook.
     */
    public function create(Schedule $schedule, array $data): Appointment
    {
        $data['schedule_id'] = $schedule->id;
        $data['duration_minutes'] = $this->calcDuration($data['starts_at'], $data['ends_at']);

        $appointment = Appointment::create($data);

        // Sincroniza com calendários externos em background
        SyncCalendarJob::dispatch($appointment, 'create');

        return $appointment->load('client');
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        if (isset($data['starts_at'], $data['ends_at'])) {
            $data['duration_minutes'] = $this->calcDuration($data['starts_at'], $data['ends_at']);
        }

        $appointment->update($data);
        SyncCalendarJob::dispatch($appointment->fresh(), 'update');

        return $appointment->fresh('client');
    }

    public function delete(Appointment $appointment): void
    {
        SyncCalendarJob::dispatch($appointment, 'delete');
        $appointment->delete();
    }

    public function updateStatus(Appointment $appointment, string $status): Appointment
    {
        $appointment->update(['status' => $status]);
        return $appointment->fresh();
    }

    private function calcDuration(string $start, string $end): int
    {
        return (int) (strtotime($end) - strtotime($start)) / 60;
    }
}

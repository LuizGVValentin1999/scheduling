<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Retorna JSON para ser consumido pelos componentes React.
 * As views Blade montam o React com os dados iniciais via data-props.
 */
class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $service) {}

    /**
     * Lista agendamentos de uma agenda.
     * Usado pelo componente Calendar.jsx via fetch.
     * GET /schedules/{schedule}/appointments?start=...&end=...
     */
    public function index(Schedule $schedule, Request $request): JsonResponse
    {
        $this->authorize('view', $schedule);

        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after:start',
        ]);

        $appointments = $this->service->listForSchedule(
            $schedule,
            $request->start,
            $request->end
        );

        return response()->json($appointments);
    }

    /**
     * Cria agendamento.
     * POST /schedules/{schedule}/appointments
     */
    public function store(Schedule $schedule, Request $request): JsonResponse
    {
        $this->authorize('create', [Appointment::class, $schedule]);

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'starts_at'    => 'required|date',
            'ends_at'      => 'required|date|after:starts_at',
            'status'       => 'in:pending,confirmed,cancelled',
            'client_id'    => 'nullable|exists:clients,id',
            'client_name'  => 'nullable|string|max:255',
            'client_email' => 'nullable|email',
            'client_phone' => 'nullable|string|max:30',
        ]);

        $appointment = $this->service->create($schedule, $data);

        return response()->json($appointment, 201);
    }

    /**
     * Exibe um agendamento.
     * GET /appointments/{appointment}
     */
    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);

        return response()->json($appointment->load('client', 'schedule'));
    }

    /**
     * Atualiza agendamento.
     * PUT /appointments/{appointment}
     */
    public function update(Appointment $appointment, Request $request): JsonResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'starts_at'    => 'sometimes|date',
            'ends_at'      => 'sometimes|date|after:starts_at',
            'status'       => 'sometimes|in:pending,confirmed,cancelled',
            'client_id'    => 'nullable|exists:clients,id',
            'client_name'  => 'nullable|string|max:255',
            'client_email' => 'nullable|email',
        ]);

        return response()->json($this->service->update($appointment, $data));
    }

    /**
     * Remove agendamento (soft delete).
     * DELETE /appointments/{appointment}
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorize('delete', $appointment);
        $this->service->delete($appointment);

        return response()->json(null, 204);
    }

    /**
     * Atualiza apenas o status (confirmar, cancelar).
     * PATCH /appointments/{appointment}/status
     */
    public function updateStatus(Appointment $appointment, Request $request): JsonResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validate(['status' => 'required|in:pending,confirmed,cancelled']);

        return response()->json($this->service->updateStatus($appointment, $data['status']));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PublicBookingLink;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleService $service) {}

    public function create(): View
    {
        return view('schedules.create');
    }

    public function edit(Schedule $schedule): View
    {
        $this->authorize('update', $schedule);

        return view('schedules.edit', compact('schedule'));
    }

    /**
     * Lista agendas visíveis para o usuário autenticado.
     * Renderiza view Blade com dados iniciais passados para o React.
     */
    public function index(): View
    {
        $schedules = $this->service->visibleFor(auth()->user());

        return view('schedules.index', compact('schedules'));
    }

    /**
     * Exibe uma agenda com o calendário React embutido.
     */
    public function show(Schedule $schedule): View
    {
        $this->authorize('view', $schedule);

        $canEdit = auth()->user()->can('update', $schedule);

        return view('schedules.show', compact('schedule', 'canEdit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'slot_duration' => 'integer|min:15|max:480',
            'is_public'     => 'boolean',
            'working_hours' => 'nullable|array',
        ]);

        $schedule = $this->service->create(auth()->user(), $data);

        return redirect()->route('schedules.show', $schedule)
                         ->with('success', 'Agenda criada com sucesso.');
    }

    public function update(Schedule $schedule, Request $request): RedirectResponse
    {
        $this->authorize('update', $schedule);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'slot_duration' => 'integer|min:15|max:480',
            'is_public'     => 'boolean',
            'working_hours' => 'nullable|array',
        ]);

        $this->service->update($schedule, $data);

        return back()->with('success', 'Agenda atualizada.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);
        $this->service->delete($schedule);

        return redirect()->route('schedules.index')
                         ->with('success', 'Agenda removida.');
    }

    // --------------------------------------------------------
    // Horários de trabalho
    // --------------------------------------------------------

    public function editWorkingHours(Schedule $schedule): View
    {
        $this->authorize('update', $schedule);

        return view('schedules.working-hours', compact('schedule'));
    }

    public function saveWorkingHours(Schedule $schedule, Request $request): RedirectResponse
    {
        $this->authorize('update', $schedule);

        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $working = [];

        foreach ($days as $day) {
            $active = $request->boolean("days.{$day}.active");
            $start  = $request->input("days.{$day}.start", '09:00');
            $end    = $request->input("days.{$day}.end",   '18:00');

            $working[$day] = [
                'active' => $active,
                'start'  => $active ? $start : '09:00',
                'end'    => $active ? $end   : '18:00',
            ];
        }

        $schedule->update(['working_hours' => $working]);

        return redirect()->route('schedules.working-hours', $schedule)
                         ->with('success', 'Horários salvos com sucesso.');
    }

    // --------------------------------------------------------
    // Links públicos
    // --------------------------------------------------------

    public function createBookingLink(Schedule $schedule, Request $request): RedirectResponse
    {
        $this->authorize('update', $schedule);

        $data = $request->validate([
            'label'    => 'nullable|string|max:100',
            'settings' => 'nullable|array',
        ]);

        $link = $this->service->createPublicLink($schedule, $data);

        return back()->with([
            'success'    => 'Link público criado.',
            'link_url'   => $link->public_url,
            'link_token' => $link->token,
        ]);
    }

    public function revokeBookingLink(Schedule $schedule, PublicBookingLink $link): RedirectResponse
    {
        $this->authorize('update', $schedule);
        $this->service->revokePublicLink($link);

        return back()->with('success', 'Link revogado.');
    }
}

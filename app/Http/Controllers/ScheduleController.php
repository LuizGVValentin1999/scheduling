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

<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleShare;
use App\Models\User;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleShareController extends Controller
{
    public function __construct(private readonly ScheduleService $service) {}

    /**
     * Exibe a tela de gerenciamento de compartilhamento.
     */
    public function index(Schedule $schedule): View
    {
        $this->authorize('share', $schedule);

        $shares = $schedule->shares()->with('sharedWith')->get();

        // Usuários do tenant que ainda não têm compartilhamento
        $sharedIds = $shares->pluck('shared_with_user_id')->push(auth()->id());
        $tenantUsers = auth()->user()->currentTenant
            ->users()
            ->whereNotIn('users.id', $sharedIds)
            ->get();

        return view('schedules.share', compact('schedule', 'shares', 'tenantUsers'));
    }

    /**
     * Compartilha a agenda com um usuário.
     * POST /schedules/{schedule}/shares
     */
    public function store(Schedule $schedule, Request $request): RedirectResponse
    {
        $this->authorize('share', $schedule);

        $data = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'permission' => 'required|in:view,edit',
        ]);

        $targetUser = User::findOrFail($data['user_id']);
        $this->service->share($schedule, $targetUser, $data['permission']);

        return back()->with('success', "Agenda compartilhada com {$targetUser->name}.");
    }

    /**
     * Atualiza permissão de compartilhamento.
     * PATCH /schedules/{schedule}/shares/{share}
     */
    public function update(Schedule $schedule, ScheduleShare $share, Request $request): RedirectResponse
    {
        $this->authorize('share', $schedule);

        $data = $request->validate(['permission' => 'required|in:view,edit']);
        $share->update($data);

        return back()->with('success', 'Permissão atualizada.');
    }

    /**
     * Remove compartilhamento.
     * DELETE /schedules/{schedule}/shares/{share}
     */
    public function destroy(Schedule $schedule, ScheduleShare $share): RedirectResponse
    {
        $this->authorize('share', $schedule);
        $share->delete();

        return back()->with('success', 'Compartilhamento removido.');
    }
}

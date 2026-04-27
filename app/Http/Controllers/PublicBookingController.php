<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PublicBookingLink;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Agendamento público — sem autenticação.
 * Acessível pelo token do link público.
 */
class PublicBookingController extends Controller
{
    public function __construct(private readonly AppointmentService $service) {}

    /**
     * Exibe a página pública de agendamento (widget embutível via Blade + React).
     */
    public function show(string $token): View
    {
        $link = PublicBookingLink::where('token', $token)->firstOrFail();

        abort_if(! $link->isValid(), 410, 'Este link de agendamento expirou ou foi desativado.');

        $schedule = $link->schedule()->with('user')->firstOrFail();

        return view('public.book', compact('link', 'schedule'));
    }

    /**
     * Recebe o formulário de agendamento público.
     */
    public function store(string $token, Request $request): RedirectResponse
    {
        $link = PublicBookingLink::where('token', $token)->firstOrFail();

        abort_if(! $link->isValid(), 410);

        // Resolve o tenant_id do schedule para o global scope não bloquear
        $schedule = $link->schedule;

        // Força o tenant_id correto para o appointment poder ser criado sem sessão
        $data = $request->validate([
            'client_name'  => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'nullable|string|max:30',
            'starts_at'    => 'required|date|after:now',
            'ends_at'      => 'required|date|after:starts_at',
            'description'  => 'nullable|string',
        ]);

        // Injeta o tenant_id manualmente porque não há usuário autenticado
        $data['tenant_id'] = $schedule->tenant_id;
        $data['title']     = "Agendamento — {$data['client_name']}";
        $data['status']    = 'pending';

        // Cria sem o global scope ativo (usuário não está logado)
        Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->create(array_merge($data, ['schedule_id' => $schedule->id, 'duration_minutes' => (int)(strtotime($data['ends_at']) - strtotime($data['starts_at'])) / 60]));

        return redirect()->route('public.book', $token)
                         ->with('success', 'Agendamento realizado! Você receberá uma confirmação em breve.');
    }
}

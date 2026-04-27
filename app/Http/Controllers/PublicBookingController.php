<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PublicBookingLink;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Agendamento público — sem autenticação.
 * Acessível pelo token do link público.
 */
class PublicBookingController extends Controller
{
    public function __construct(private readonly AppointmentService $service) {}

    /**
     * Exibe a página pública de agendamento.
     * Retorna JSON quando solicitado pelo widget React (Accept: application/json).
     */
    public function show(string $token, Request $request): View|JsonResponse
    {
        $link = PublicBookingLink::where('token', $token)->firstOrFail();

        abort_if(! $link->isValid(), 410, 'Este link de agendamento expirou ou foi desativado.');

        $schedule = $link->schedule()->with('user')->firstOrFail();

        if ($request->expectsJson()) {
            return response()->json([
                'schedule' => [
                    'id'            => $schedule->id,
                    'name'          => $schedule->name,
                    'description'   => $schedule->description,
                    'slot_duration' => $schedule->slot_duration,
                    'working_hours' => $schedule->working_hours,
                    'is_public'     => $schedule->is_public,
                    'user'          => [
                        'id'   => $schedule->user->id,
                        'name' => $schedule->user->name,
                    ],
                ],
            ]);
        }

        return view('public.book', compact('link', 'schedule'));
    }

    /**
     * Recebe o formulário de agendamento público.
     * Suporta tanto POST via form Blade quanto fetch JSON do widget React.
     */
    public function store(string $token, Request $request): RedirectResponse|JsonResponse
    {
        $link = PublicBookingLink::where('token', $token)->firstOrFail();

        abort_if(! $link->isValid(), 410);

        $schedule = $link->schedule;

        $data = $request->validate([
            'client_name'  => 'required|string|max:255',
            'client_email' => 'nullable|email|required_without:client_phone',
            'client_phone' => 'nullable|string|max:30|required_without:client_email',
            'starts_at'    => 'required|date|after:now',
            'ends_at'      => 'required|date|after:starts_at',
            'description'  => 'nullable|string',
        ], [
            'client_email.required_without' => 'Informe o e-mail ou o telefone.',
            'client_phone.required_without' => 'Informe o telefone ou o e-mail.',
        ]);

        $data['tenant_id']        = $schedule->tenant_id;
        $data['title']            = "Agendamento — {$data['client_name']}";
        $data['status']           = 'pending';
        $data['schedule_id']      = $schedule->id;
        $data['duration_minutes'] = (int) ((strtotime($data['ends_at']) - strtotime($data['starts_at'])) / 60);

        Appointment::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->create($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Agendamento realizado com sucesso.'], 201);
        }

        return redirect()->route('public.book', $token)
                         ->with('success', 'Agendamento realizado! Você receberá uma confirmação em breve.');
    }
}

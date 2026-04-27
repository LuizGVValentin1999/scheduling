<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Schedule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        // Stats passados como props para o componente DashboardStats (React)
        $stats = [
            'total_appointments_today' => Appointment::whereDate('starts_at', today())
                ->whereHas('schedule', fn($q) => $q->where('user_id', $user->id))
                ->count(),

            'total_appointments_week' => Appointment::inRange(now()->startOfWeek(), now()->endOfWeek())
                ->whereHas('schedule', fn($q) => $q->where('user_id', $user->id))
                ->count(),

            'pending_count' => Appointment::where('status', 'pending')
                ->whereHas('schedule', fn($q) => $q->where('user_id', $user->id))
                ->count(),

            'total_schedules' => Schedule::where('user_id', $user->id)->count(),
        ];

        // Próximos agendamentos para o mini-calendário
        $upcoming = Appointment::upcoming()
            ->whereHas('schedule', fn($q) => $q->where('user_id', $user->id))
            ->with('client', 'schedule')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'upcoming'));
    }
}

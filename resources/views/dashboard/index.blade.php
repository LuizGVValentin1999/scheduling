@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div>

    <div class="page-header">
        <div>
            <h1 class="page-title">Olá, {{ auth()->user()->name }}</h1>
            <div class="page-title-sub">{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</div>
        </div>
        <a href="{{ route('schedules.create') }}" class="btn-new-agenda">
            <span class="material-symbols-rounded">add</span>
            Nova agenda
        </a>
    </div>

    {{-- Stats mount point (React) com fallback SSR --}}
    <div id="dashboard-stats"
         data-stats="{{ json_encode($stats) }}"
         data-upcoming="{{ json_encode($upcoming) }}">
        <div class="stats-grid">
            @foreach([
                ['label' => 'Hoje',        'value' => $stats['total_appointments_today'], 'icon' => 'today',            'color' => '#3b82f6', 'bg' => '#eff6ff'],
                ['label' => 'Esta semana', 'value' => $stats['total_appointments_week'],  'icon' => 'date_range',       'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
                ['label' => 'Pendentes',   'value' => $stats['pending_count'],            'icon' => 'schedule',         'color' => '#f59e0b', 'bg' => '#fffbeb'],
                ['label' => 'Agendas',     'value' => $stats['total_schedules'],          'icon' => 'calendar_month',   'color' => '#10b981', 'bg' => '#ecfdf5'],
            ] as $stat)
                <div class="stat-card">
                    <div class="stat-card-icon" style="background:{{ $stat['bg'] }}; color:{{ $stat['color'] }};">
                        <span class="material-symbols-rounded" style="font-size:22px; font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;">{{ $stat['icon'] }}</span>
                    </div>
                    <div class="stat-card-body">
                        <div class="stat-card-value" style="color:{{ $stat['color'] }};">{{ $stat['value'] }}</div>
                        <div class="stat-card-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="dashboard-grid">

        {{-- Próximos agendamentos --}}
        <div class="card">
            <h2 class="card-title">
                <span class="material-symbols-rounded" style="font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;">event_upcoming</span>
                Próximos agendamentos
            </h2>
            @forelse($upcoming as $appt)
                <div class="appt-row">
                    <div class="appt-dot appt-dot--{{ $appt->status }}"></div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:14px; font-weight:500; color:#1e1e2e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $appt->title }}</div>
                        <div style="font-size:12px; color:#6c7086; margin-top:2px;">
                            {{ $appt->resolvedClientName() }} · {{ $appt->starts_at->format('d/m H:i') }}
                        </div>
                    </div>
                    <a href="{{ route('schedules.show', $appt->schedule_id) }}" class="appt-link">
                        <span class="material-symbols-rounded" style="font-size:16px;">arrow_forward</span>
                    </a>
                </div>
            @empty
                <div style="text-align:center; padding:24px 0; color:#9ca3af; font-size:14px;">
                    <span class="material-symbols-rounded" style="font-size:36px; display:block; margin-bottom:8px; opacity:.4;">event_busy</span>
                    Nenhum agendamento próximo.
                </div>
            @endforelse
        </div>

        {{-- Ações rápidas --}}
        <div class="card">
            <h2 class="card-title">
                <span class="material-symbols-rounded" style="font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;">bolt</span>
                Ações rápidas
            </h2>
            <a href="{{ route('schedules.index') }}" class="quick-action quick-action--purple">
                <span class="material-symbols-rounded">calendar_month</span>
                Minhas agendas
            </a>
            <a href="{{ route('schedules.create') }}" class="quick-action quick-action--blue">
                <span class="material-symbols-rounded">add_circle</span>
                Nova agenda
            </a>
            @if(auth()->user()->isAdminOfCurrentTenant())
                <a href="{{ route('admin.dashboard') }}" class="quick-action quick-action--amber">
                    <span class="material-symbols-rounded">admin_panel_settings</span>
                    Painel Admin
                </a>
            @endif
        </div>

    </div>
</div>

<style>
.btn-new-agenda {
    display: inline-flex; align-items: center; gap: 6px;
    background: #7c3aed; color: #fff; padding: 10px 18px;
    border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;
    transition: background .15s;
}
.btn-new-agenda:hover { background: #6d28d9; }
.btn-new-agenda .material-symbols-rounded { font-size: 18px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
    width: 100%;
}
.stat-card {
    background: #fff; border-radius: 12px; padding: 18px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.03);
    display: flex; align-items: center; gap: 14px;
}
.stat-card-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-card-value { font-size: 26px; font-weight: 700; line-height: 1; }
.stat-card-label { font-size: 12px; color: #6c7086; margin-top: 3px; }

.dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 20px;
    width: 100%;
    align-items: start;
}
.card-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 15px; font-weight: 600; color: #1e1e2e;
    margin: 0 0 16px 0;
}
.card-title .material-symbols-rounded { font-size: 18px; color: #7c3aed; }

.appt-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid #f1f5f9;
}
.appt-row:last-child { border-bottom: none; }
.appt-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.appt-dot--confirmed { background: #10b981; }
.appt-dot--pending   { background: #f59e0b; }
.appt-dot--cancelled { background: #ef4444; }
.appt-link {
    color: #7c3aed; text-decoration: none; display: flex; align-items: center;
    padding: 4px; border-radius: 6px; transition: background .15s;
}
.appt-link:hover { background: #f5f3ff; }

.quick-action {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; border-radius: 8px;
    text-decoration: none; font-size: 14px; font-weight: 500;
    margin-bottom: 10px; transition: filter .15s;
}
.quick-action:last-child { margin-bottom: 0; }
.quick-action:hover { filter: brightness(0.95); }
.quick-action .material-symbols-rounded {
    font-size: 18px;
    font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
}
.quick-action--purple { background: #f5f3ff; color: #7c3aed; }
.quick-action--blue   { background: #eff6ff; color: #2563eb; }
.quick-action--amber  { background: #fffbeb; color: #92400e; }

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .dashboard-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .stat-card { padding: 14px; }
    .stat-card-value { font-size: 22px; }
}
</style>

@endsection

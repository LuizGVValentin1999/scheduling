@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div style="max-width:1200px;">

    <h1 style="font-size:22px; font-weight:700; margin-bottom:4px; color:#1e1e2e;">
        Olá, {{ auth()->user()->name }} 👋
    </h1>
    <p style="color:#6c7086; margin-bottom:24px; font-size:14px;">
        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    </p>

    {{--
        Mount point do componente React DashboardStats.
        Os dados são serializados no atributo data-props para hidratação inicial.
        O componente faz fetch de atualizações, mas já inicia com dados do server.
    --}}
    <div id="dashboard-stats"
         data-stats="{{ json_encode($stats) }}"
         data-upcoming="{{ json_encode($upcoming) }}">
        {{-- Fallback SSR enquanto o JS não carrega --}}
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
            @foreach([
                ['label' => 'Agendamentos hoje', 'value' => $stats['total_appointments_today'], 'color' => '#3b82f6'],
                ['label' => 'Esta semana',        'value' => $stats['total_appointments_week'],  'color' => '#8b5cf6'],
                ['label' => 'Pendentes',           'value' => $stats['pending_count'],            'color' => '#f59e0b'],
                ['label' => 'Minhas agendas',      'value' => $stats['total_schedules'],          'color' => '#10b981'],
            ] as $stat)
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                    <div style="font-size:28px; font-weight:700; color:{{ $stat['color'] }};">{{ $stat['value'] }}</div>
                    <div style="font-size:13px; color:#6c7086; margin-top:4px;">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">

        {{-- Próximos agendamentos --}}
        <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">Próximos agendamentos</h2>
            @forelse($upcoming as $appt)
                <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f1f5f9;">
                    <div style="width:8px; height:8px; border-radius:50%;
                                background:{{ $appt->status === 'confirmed' ? '#10b981' : ($appt->status === 'pending' ? '#f59e0b' : '#ef4444') }};
                                flex-shrink:0;">
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:14px; font-weight:500;">{{ $appt->title }}</div>
                        <div style="font-size:12px; color:#6c7086;">
                            {{ $appt->resolvedClientName() }} · {{ $appt->starts_at->format('d/m H:i') }}
                        </div>
                    </div>
                    <a href="{{ route('schedules.show', $appt->schedule_id) }}"
                       style="font-size:12px; color:#3b82f6; text-decoration:none;">Ver →</a>
                </div>
            @empty
                <p style="color:#9ca3af; font-size:14px;">Nenhum agendamento próximo.</p>
            @endforelse
        </div>

        {{-- Ações rápidas --}}
        <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">Ações rápidas</h2>
            <a href="{{ route('schedules.index') }}"
               style="display:block; background:#f5f3ff; color:#7c3aed; padding:12px; border-radius:8px;
                      text-decoration:none; font-size:14px; font-weight:500; margin-bottom:10px; text-align:center;">
                📆 Minhas agendas
            </a>
            <a href="{{ route('schedules.create') }}"
               style="display:block; background:#eff6ff; color:#2563eb; padding:12px; border-radius:8px;
                      text-decoration:none; font-size:14px; font-weight:500; margin-bottom:10px; text-align:center;">
                ➕ Nova agenda
            </a>
            @if(auth()->user()->isAdminOfCurrentTenant())
                <a href="{{ route('admin.dashboard') }}"
                   style="display:block; background:#fef3c7; color:#92400e; padding:12px; border-radius:8px;
                          text-decoration:none; font-size:14px; font-weight:500; text-align:center;">
                    ⚙️ Painel Admin
                </a>
            @endif
        </div>

    </div>
</div>

@endsection

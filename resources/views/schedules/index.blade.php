@extends('layouts.app')

@section('title', 'Agendas')

@section('content')

<div>

    <div class="page-header">
        <div>
            <h1 class="page-title">Agendas</h1>
            <div class="page-title-sub">Gerencie suas agendas de atendimento</div>
        </div>
        <a href="{{ route('schedules.create') }}" class="btn-new">
            <span class="material-symbols-rounded">add</span>
            Nova agenda
        </a>
    </div>

    @forelse($schedules as $schedule)
        <div class="schedule-card">
            <div class="schedule-icon">
                <span class="material-symbols-rounded">calendar_month</span>
            </div>
            <div class="schedule-info">
                <div class="schedule-name">{{ $schedule->name }}</div>
                <div class="schedule-meta">
                    <span class="meta-badge meta-badge--gray">
                        <span class="material-symbols-rounded">person</span>
                        {{ $schedule->user->name }}
                    </span>
                    @if(auth()->id() !== $schedule->user_id)
                        <span class="meta-badge meta-badge--purple">
                            <span class="material-symbols-rounded">share</span>
                            Compartilhada
                        </span>
                    @endif
                    <span class="meta-badge meta-badge--gray">
                        <span class="material-symbols-rounded">timer</span>
                        {{ $schedule->slot_duration }}min
                    </span>
                    @if($schedule->is_public)
                        <span class="meta-badge meta-badge--green">
                            <span class="material-symbols-rounded">public</span>
                            Pública
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('schedules.show', $schedule) }}" class="btn-open">
                Abrir
                <span class="material-symbols-rounded">arrow_forward</span>
            </a>
        </div>
    @empty
        <div class="empty-state">
            <span class="material-symbols-rounded empty-state-icon">calendar_month</span>
            <h2 class="empty-state-title">Nenhuma agenda ainda</h2>
            <p class="empty-state-desc">Crie sua primeira agenda para começar a receber agendamentos.</p>
            <a href="{{ route('schedules.create') }}" class="btn-new">
                <span class="material-symbols-rounded">add</span>
                Criar primeira agenda
            </a>
        </div>
    @endforelse

</div>

<style>
.btn-new {
    display: inline-flex; align-items: center; gap: 6px;
    background: #7c3aed; color: #fff; padding: 10px 18px;
    border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;
    transition: background .15s; white-space: nowrap;
}
.btn-new:hover { background: #6d28d9; }
.btn-new .material-symbols-rounded { font-size: 18px; }

.schedule-card {
    background: #fff; border-radius: 12px; padding: 16px 20px;
    margin-bottom: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06);
    display: flex; align-items: center; gap: 16px;
    transition: box-shadow .15s;
}
.schedule-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }

.schedule-icon {
    width: 44px; height: 44px; border-radius: 10px;
    background: #f5f3ff; color: #7c3aed;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.schedule-icon .material-symbols-rounded {
    font-size: 22px;
    font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
}

.schedule-info { flex: 1; min-width: 0; }
.schedule-name { font-size: 15px; font-weight: 600; color: #1e1e2e; margin-bottom: 6px; }

.schedule-meta { display: flex; flex-wrap: wrap; gap: 6px; }
.meta-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 999px;
    font-size: 11px; font-weight: 500;
}
.meta-badge .material-symbols-rounded { font-size: 12px; }
.meta-badge--gray   { background: #f1f5f9; color: #64748b; }
.meta-badge--purple { background: #f5f3ff; color: #7c3aed; }
.meta-badge--green  { background: #ecfdf5; color: #059669; }

.btn-open {
    display: inline-flex; align-items: center; gap: 4px;
    background: #eff6ff; color: #2563eb; padding: 8px 14px;
    border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500;
    white-space: nowrap; transition: background .15s; flex-shrink: 0;
}
.btn-open:hover { background: #dbeafe; }
.btn-open .material-symbols-rounded { font-size: 16px; }

.empty-state {
    background: #fff; border-radius: 12px; padding: 48px 24px;
    text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.empty-state-icon {
    font-size: 48px; color: #cba6f7; display: block; margin-bottom: 12px;
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;
}
.empty-state-title { font-size: 18px; font-weight: 600; color: #1e1e2e; margin: 0 0 8px; }
.empty-state-desc  { font-size: 14px; color: #6c7086; margin: 0 0 20px; }

@media (max-width: 600px) {
    .schedule-card { flex-wrap: wrap; }
    .btn-open { width: 100%; justify-content: center; }
}
</style>

@endsection

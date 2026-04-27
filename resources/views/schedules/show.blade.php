@extends('layouts.app')

@section('title', $schedule->name)

@section('content')

<div>

    <div class="page-header">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
                <a href="{{ route('schedules.index') }}" class="breadcrumb-back">
                    <span class="material-symbols-rounded">arrow_back</span>
                </a>
                <h1 class="page-title">{{ $schedule->name }}</h1>
            </div>
            <div class="page-title-sub">
                <span class="material-symbols-rounded" style="font-size:13px; vertical-align:middle;">person</span>
                {{ $schedule->user->name }}
                &nbsp;·&nbsp;
                <span class="material-symbols-rounded" style="font-size:13px; vertical-align:middle;">timer</span>
                {{ $schedule->slot_duration }}min por slot
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            @if($canEdit)
                <button onclick="window.dispatchEvent(new CustomEvent('calendar:new-appointment'))"
                        class="btn-action btn-action--green">
                    <span class="material-symbols-rounded">add_circle</span>
                    Novo agendamento
                </button>
                <a href="{{ route('schedules.working-hours', $schedule) }}" class="btn-action btn-action--gray">
                    <span class="material-symbols-rounded">schedule</span>
                    Horários
                </a>
                <a href="{{ route('schedules.edit', $schedule) }}" class="btn-action btn-action--gray">
                    <span class="material-symbols-rounded">edit</span>
                    Editar
                </a>
            @endif
            @can('share', $schedule)
                <a href="{{ route('schedules.share', $schedule) }}" class="btn-action btn-action--purple">
                    <span class="material-symbols-rounded">group_add</span>
                    Compartilhar
                </a>
                <form method="POST" action="{{ route('schedules.booking-links.store', $schedule) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-action--blue">
                        <span class="material-symbols-rounded">link</span>
                        Link de agendamento
                    </button>
                </form>
            @endcan
            {{-- Link público existente --}}
            @if($schedule->publicLinks()->where('is_active', true)->exists())
                @php $activeLink = $schedule->publicLinks()->where('is_active', true)->latest()->first(); @endphp
                <a href="{{ route('public.book', $activeLink->token) }}" target="_blank"
                   class="btn-action btn-action--gray" title="Abrir página de agendamento público">
                    <span class="material-symbols-rounded">open_in_new</span>
                    Ver página pública
                </a>
            @endif
        </div>
    </div>

    @if(session('link_url'))
        <div class="flash flash--success" style="align-items:flex-start;">
            <span class="material-symbols-rounded">link</span>
            <div>
                <strong>Link público gerado:</strong><br>
                <a href="{{ session('link_url') }}" target="_blank"
                   style="color:#166534; font-weight:600; word-break:break-all;">
                    {{ session('link_url') }}
                </a>
            </div>
        </div>
    @endif

    {{-- Calendar React mount point --}}
    <div id="calendar-root"
         data-schedule-id="{{ $schedule->id }}"
         data-can-edit="{{ $canEdit ? 'true' : 'false' }}"
         data-csrf="{{ csrf_token() }}"
         class="card" style="padding:20px;">
        <div style="display:flex; align-items:center; justify-content:center; height:400px; color:#9ca3af;">
            <div style="text-align:center;">
                <span class="material-symbols-rounded" style="font-size:40px; display:block; margin-bottom:8px; opacity:.3;">calendar_month</span>
                Carregando calendário…
            </div>
        </div>
    </div>

</div>

<style>
.breadcrumb-back {
    color: #6c7086; text-decoration: none; display: flex; align-items: center;
    padding: 4px; border-radius: 6px; transition: background .15s, color .15s;
}
.breadcrumb-back:hover { background: #f1f5f9; color: #1e1e2e; }
.breadcrumb-back .material-symbols-rounded { font-size: 20px; }

.btn-action {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 8px;
    text-decoration: none; font-size: 13px; font-weight: 500;
    border: none; cursor: pointer; transition: filter .15s; white-space: nowrap;
}
.btn-action:hover { filter: brightness(0.93); }
.btn-action .material-symbols-rounded { font-size: 16px; }
.btn-action--green  { background: #10b981; color: #fff; }
.btn-action--green:hover { filter: brightness(0.9); }
.btn-action--purple { background: #f5f3ff; color: #7c3aed; }
.btn-action--blue   { background: #eff6ff; color: #2563eb; }
.btn-action--gray   { background: #f1f5f9; color: #475569; }

@media (max-width: 600px) {
    .page-header { gap: 16px; }
    .page-header > div:last-child { width: 100%; }
    .btn-action { flex: 1; justify-content: center; }
}
</style>

@endsection

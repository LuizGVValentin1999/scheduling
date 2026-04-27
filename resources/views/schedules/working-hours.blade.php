@extends('layouts.app')

@section('title', 'Horários — ' . $schedule->name)

@section('content')

@php
$days = [
    'mon' => 'Segunda-feira',
    'tue' => 'Terça-feira',
    'wed' => 'Quarta-feira',
    'thu' => 'Quinta-feira',
    'fri' => 'Sexta-feira',
    'sat' => 'Sábado',
    'sun' => 'Domingo',
];

$defaults = [
    'mon' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
    'tue' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
    'wed' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
    'thu' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
    'fri' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
    'sat' => ['active' => false, 'start' => '09:00', 'end' => '13:00'],
    'sun' => ['active' => false, 'start' => '09:00', 'end' => '13:00'],
];

$wh = $schedule->working_hours ?? $defaults;
@endphp

<div style="max-width:760px;">

    <div class="page-header" style="margin-bottom:24px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <a href="{{ route('schedules.show', $schedule) }}" class="breadcrumb-back">
                <span class="material-symbols-rounded">arrow_back</span>
            </a>
            <div>
                <h1 class="page-title">Horários de atendimento</h1>
                <div class="page-title-sub">{{ $schedule->name }} · {{ $schedule->slot_duration }}min por slot</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('schedules.working-hours.save', $schedule) }}" id="wh-form">
        @csrf

        {{-- Atalhos rápidos --}}
        <div class="card wh-shortcuts">
            <div class="wh-shortcuts-label">
                <span class="material-symbols-rounded">bolt</span>
                Atalhos
            </div>
            <div class="wh-shortcuts-btns">
                <button type="button" onclick="applyPreset('weekdays')" class="btn-shortcut">
                    <span class="material-symbols-rounded">work</span>
                    Seg–Sex
                </button>
                <button type="button" onclick="applyPreset('alldays')" class="btn-shortcut">
                    <span class="material-symbols-rounded">calendar_month</span>
                    Todos os dias
                </button>
                <button type="button" onclick="applyPreset('none')" class="btn-shortcut btn-shortcut--danger">
                    <span class="material-symbols-rounded">block</span>
                    Limpar tudo
                </button>
            </div>
        </div>

        {{-- Dias da semana --}}
        <div class="card" style="padding:0; overflow:hidden;">

            {{-- Cabeçalho --}}
            <div class="wh-table-head">
                <span>Dia</span>
                <span>Ativo</span>
                <span>Início</span>
                <span>Fim</span>
                <span>Copiar para todos</span>
            </div>

            @foreach($days as $key => $label)
            @php
                $h      = $wh[$key] ?? $defaults[$key];
                $active = (bool) ($h['active'] ?? false);
                $start  = $h['start'] ?? '09:00';
                $end    = $h['end']   ?? '18:00';
            @endphp

            <div class="wh-row" id="row-{{ $key }}" data-day="{{ $key }}">
                <div class="wh-day-label">
                    <span class="day-dot day-dot--{{ $active ? 'on' : 'off' }}" id="dot-{{ $key }}"></span>
                    <span class="day-name">{{ $label }}</span>
                    <span class="day-abbr">{{ Str::upper(substr($label, 0, 3)) }}</span>
                </div>

                <div class="wh-toggle-cell">
                    <label class="toggle" title="{{ $active ? 'Desativar' : 'Ativar' }} {{ $label }}">
                        <input type="checkbox"
                               name="days[{{ $key }}][active]"
                               value="1"
                               id="chk-{{ $key }}"
                               {{ $active ? 'checked' : '' }}
                               onchange="toggleDay('{{ $key }}', this.checked)">
                        <span class="toggle-track">
                            <span class="toggle-thumb"></span>
                        </span>
                    </label>
                </div>

                <div class="wh-time-cell">
                    <input type="time"
                           name="days[{{ $key }}][start]"
                           id="start-{{ $key }}"
                           value="{{ $start }}"
                           class="time-input"
                           {{ ! $active ? 'disabled' : '' }}
                           onchange="updatePreview('{{ $key }}')">
                </div>

                <div class="wh-time-cell">
                    <input type="time"
                           name="days[{{ $key }}][end]"
                           id="end-{{ $key }}"
                           value="{{ $end }}"
                           class="time-input"
                           {{ ! $active ? 'disabled' : '' }}
                           onchange="updatePreview('{{ $key }}')">
                </div>

                <div class="wh-copy-cell">
                    <button type="button"
                            onclick="copyToAll('{{ $key }}')"
                            class="btn-copy"
                            {{ ! $active ? 'disabled' : '' }}
                            id="copy-{{ $key }}"
                            title="Copiar horário de {{ $label }} para todos os dias ativos">
                        <span class="material-symbols-rounded">content_copy</span>
                        <span class="btn-copy-label">Copiar</span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Preview visual --}}
        <div class="card wh-preview" id="wh-preview">
            <div class="wh-preview-title">
                <span class="material-symbols-rounded">preview</span>
                Visualização da semana
            </div>
            <div class="wh-preview-grid" id="preview-grid">
                @foreach($days as $key => $label)
                @php
                    $h      = $wh[$key] ?? $defaults[$key];
                    $active = (bool) ($h['active'] ?? false);
                    $start  = $h['start'] ?? '09:00';
                    $end    = $h['end']   ?? '18:00';
                @endphp
                <div class="preview-day {{ $active ? 'preview-day--on' : 'preview-day--off' }}"
                     id="preview-{{ $key }}">
                    <div class="preview-day-name">{{ Str::upper(substr($label, 0, 3)) }}</div>
                    <div class="preview-day-hours" id="preview-hours-{{ $key }}">
                        @if($active)
                            {{ $start }}<br>{{ $end }}
                        @else
                            —
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:4px;">
            <button type="submit" class="btn-save">
                <span class="material-symbols-rounded">save</span>
                Salvar horários
            </button>
            <a href="{{ route('schedules.show', $schedule) }}" class="btn-cancel">
                Cancelar
            </a>
        </div>

    </form>
</div>

<style>
/* Breadcrumb */
.breadcrumb-back {
    color: #6c7086; text-decoration: none; display: flex; align-items: center;
    padding: 4px; border-radius: 6px; transition: background .15s, color .15s;
}
.breadcrumb-back:hover { background: #f1f5f9; color: #1e1e2e; }
.breadcrumb-back .material-symbols-rounded { font-size: 20px; }

/* Shortcuts */
.wh-shortcuts {
    display: flex; align-items: center; gap: 16px;
    flex-wrap: wrap; padding: 14px 20px; margin-bottom: 16px;
}
.wh-shortcuts-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 700; color: #6c7086;
    text-transform: uppercase; letter-spacing: .05em; white-space: nowrap;
}
.wh-shortcuts-label .material-symbols-rounded { font-size: 15px; color: #7c3aed; }
.wh-shortcuts-btns { display: flex; gap: 8px; flex-wrap: wrap; }
.btn-shortcut {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 8px; border: 1.5px solid #e5e7eb;
    background: #fff; color: #374151; font-size: 13px; font-weight: 500;
    cursor: pointer; transition: all .15s;
}
.btn-shortcut:hover { border-color: #7c3aed; color: #7c3aed; background: #faf5ff; }
.btn-shortcut .material-symbols-rounded { font-size: 16px; }
.btn-shortcut--danger { color: #ef4444; border-color: #fca5a5; }
.btn-shortcut--danger:hover { border-color: #ef4444; color: #ef4444; background: #fff5f5; }

/* Table header */
.wh-table-head {
    display: grid;
    grid-template-columns: 1fr 80px 120px 120px 110px;
    padding: 10px 20px;
    font-size: 11px; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .05em;
    border-bottom: 2px solid #f1f5f9;
    background: #fafafa;
}

/* Row */
.wh-row {
    display: grid;
    grid-template-columns: 1fr 80px 120px 120px 110px;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid #f8fafc;
    transition: background .12s;
}
.wh-row:last-child { border-bottom: none; }
.wh-row:hover { background: #fafafa; }
.wh-row.wh-row--disabled { opacity: .5; }

/* Day label */
.wh-day-label { display: flex; align-items: center; gap: 10px; }
.day-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    transition: background .2s;
}
.day-dot--on  { background: #10b981; }
.day-dot--off { background: #d1d5db; }
.day-name { font-size: 14px; font-weight: 500; color: #1e1e2e; }
.day-abbr { display: none; font-size: 13px; font-weight: 600; color: #475569; }

/* Toggle */
.wh-toggle-cell { display: flex; justify-content: center; }
.toggle { display: inline-flex; cursor: pointer; }
.toggle input { display: none; }
.toggle-track {
    width: 40px; height: 22px; border-radius: 11px;
    background: #d1d5db; position: relative;
    transition: background .2s;
}
.toggle input:checked ~ .toggle-track { background: #7c3aed; }
.toggle-thumb {
    width: 16px; height: 16px; border-radius: 50%; background: #fff;
    position: absolute; top: 3px; left: 3px;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.toggle input:checked ~ .toggle-track .toggle-thumb { transform: translateX(18px); }

/* Time inputs */
.wh-time-cell { display: flex; align-items: center; }
.time-input {
    padding: 7px 10px; border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: 13px; color: #1e293b; outline: none; width: 100px;
    transition: border-color .2s; background: #fff; font-family: inherit;
}
.time-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.08); }
.time-input:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; border-color: #f1f5f9; }

/* Copy button */
.wh-copy-cell { display: flex; justify-content: center; }
.btn-copy {
    display: inline-flex; align-items: center; gap: 4px;
    background: transparent; border: 1px solid #e5e7eb; color: #64748b;
    padding: 6px 10px; border-radius: 7px; cursor: pointer;
    font-size: 12px; font-weight: 500; transition: all .15s;
}
.btn-copy:hover:not(:disabled) { border-color: #7c3aed; color: #7c3aed; background: #faf5ff; }
.btn-copy:disabled { opacity: .35; cursor: not-allowed; }
.btn-copy .material-symbols-rounded { font-size: 14px; }

/* Preview */
.wh-preview { padding: 16px 20px; margin-top: 16px; margin-bottom: 20px; }
.wh-preview-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .05em; margin-bottom: 14px;
}
.wh-preview-title .material-symbols-rounded { font-size: 16px; color: #7c3aed; }
.wh-preview-grid {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;
}
.preview-day {
    border-radius: 10px; padding: 10px 6px; text-align: center;
    transition: all .2s;
}
.preview-day--on  { background: #f5f3ff; border: 1.5px solid #ddd6fe; }
.preview-day--off { background: #f8fafc; border: 1.5px solid #f1f5f9; }
.preview-day-name {
    font-size: 11px; font-weight: 700; letter-spacing: .04em; margin-bottom: 6px;
}
.preview-day--on  .preview-day-name { color: #7c3aed; }
.preview-day--off .preview-day-name { color: #cbd5e1; }
.preview-day-hours { font-size: 11px; line-height: 1.5; }
.preview-day--on  .preview-day-hours { color: #4c1d95; }
.preview-day--off .preview-day-hours { color: #cbd5e1; }

/* Save / Cancel */
.btn-save {
    display: inline-flex; align-items: center; gap: 8px;
    background: #7c3aed; color: #fff; padding: 11px 22px;
    border-radius: 8px; border: none; cursor: pointer;
    font-size: 14px; font-weight: 600; transition: background .15s;
}
.btn-save:hover { background: #6d28d9; }
.btn-save .material-symbols-rounded { font-size: 18px; }
.btn-cancel {
    display: inline-flex; align-items: center;
    background: #f3f4f6; color: #374151; padding: 11px 22px;
    border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;
    transition: background .15s;
}
.btn-cancel:hover { background: #e5e7eb; }

/* Responsive */
@media (max-width: 680px) {
    .wh-table-head { display: none; }
    .wh-row {
        grid-template-columns: 1fr 56px;
        grid-template-rows: auto auto;
        gap: 10px;
        padding: 14px 16px;
    }
    .day-name  { display: none; }
    .day-abbr  { display: inline; }
    .wh-time-cell, .wh-copy-cell { grid-column: 1; }
    .wh-row { grid-template-columns: 1fr 56px; }
    .wh-copy-cell { display: none; }
    .wh-preview-grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 420px) {
    .wh-preview-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
const DAYS = ['mon','tue','wed','thu','fri','sat','sun'];

function toggleDay(day, active) {
    const row   = document.getElementById('row-' + day);
    const dot   = document.getElementById('dot-' + day);
    const start = document.getElementById('start-' + day);
    const end   = document.getElementById('end-' + day);
    const copy  = document.getElementById('copy-' + day);

    start.disabled = !active;
    end.disabled   = !active;
    if (copy) copy.disabled = !active;

    dot.className  = 'day-dot day-dot--' + (active ? 'on' : 'off');
    row.style.opacity = active ? '1' : '.55';

    updatePreview(day);
}

function updatePreview(day) {
    const active = document.getElementById('chk-' + day).checked;
    const start  = document.getElementById('start-' + day).value;
    const end    = document.getElementById('end-' + day).value;
    const card   = document.getElementById('preview-' + day);
    const hours  = document.getElementById('preview-hours-' + day);

    card.className = 'preview-day preview-day--' + (active ? 'on' : 'off');
    hours.innerHTML = active ? (start + '<br>' + end) : '—';
}

function copyToAll(sourceDay) {
    const start = document.getElementById('start-' + sourceDay).value;
    const end   = document.getElementById('end-'   + sourceDay).value;

    DAYS.forEach(day => {
        if (document.getElementById('chk-' + day).checked) {
            document.getElementById('start-' + day).value = start;
            document.getElementById('end-'   + day).value = end;
            updatePreview(day);
        }
    });

    // Feedback visual
    const btn = document.getElementById('copy-' + sourceDay);
    const orig = btn.innerHTML;
    btn.innerHTML = '<span class="material-symbols-rounded" style="font-size:14px;">check</span> Copiado!';
    btn.style.color = '#10b981';
    btn.style.borderColor = '#6ee7b7';
    setTimeout(() => { btn.innerHTML = orig; btn.style.color = ''; btn.style.borderColor = ''; }, 1500);
}

function applyPreset(preset) {
    const weekdays = ['mon','tue','wed','thu','fri'];
    const weekend  = ['sat','sun'];

    DAYS.forEach(day => {
        let active = false;
        if (preset === 'weekdays') active = weekdays.includes(day);
        if (preset === 'alldays')  active = true;
        if (preset === 'none')     active = false;

        const chk = document.getElementById('chk-' + day);
        chk.checked = active;
        toggleDay(day, active);
    });
}

// Inicializa estado visual ao carregar
document.addEventListener('DOMContentLoaded', () => {
    DAYS.forEach(day => {
        const active = document.getElementById('chk-' + day).checked;
        if (!active) toggleDay(day, false);
    });
});
</script>

@endsection

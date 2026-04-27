@extends('layouts.app')

@section('title', 'Agendas')

@section('content')

<div style="max-width:900px;">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
        <h1 style="font-size:20px; font-weight:700; color:#1e1e2e;">Agendas</h1>
        <a href="{{ route('schedules.create') }}"
           style="background:#7c3aed; color:#fff; padding:10px 20px; border-radius:8px;
                  text-decoration:none; font-size:14px; font-weight:600;">
            + Nova agenda
        </a>
    </div>

    @forelse($schedules as $schedule)
        <div style="background:#fff; border-radius:12px; padding:20px 24px; margin-bottom:12px;
                    box-shadow:0 1px 4px rgba(0,0,0,.06); display:flex; align-items:center; gap:16px;">
            <div style="flex:1;">
                <div style="font-size:15px; font-weight:600; color:#1e1e2e;">{{ $schedule->name }}</div>
                <div style="font-size:12px; color:#6c7086; margin-top:2px;">
                    {{ $schedule->user->name }}
                    @if(auth()->id() !== $schedule->user_id)
                        · <span style="color:#7c3aed;">Compartilhada</span>
                    @endif
                    · Slot: {{ $schedule->slot_duration }}min
                    @if($schedule->is_public)
                        · <span style="color:#10b981;">Pública</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('schedules.show', $schedule) }}"
               style="background:#eff6ff; color:#2563eb; padding:8px 16px; border-radius:6px;
                      text-decoration:none; font-size:13px; font-weight:500;">
                Abrir →
            </a>
        </div>
    @empty
        <div style="background:#fff; border-radius:12px; padding:40px; text-align:center;
                    box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <div style="font-size:40px; margin-bottom:12px;">📆</div>
            <p style="color:#6c7086; margin-bottom:16px;">Nenhuma agenda ainda.</p>
            <a href="{{ route('schedules.create') }}"
               style="background:#7c3aed; color:#fff; padding:10px 20px; border-radius:8px;
                      text-decoration:none; font-size:14px; font-weight:600;">
                Criar primeira agenda
            </a>
        </div>
    @endforelse

</div>

@endsection

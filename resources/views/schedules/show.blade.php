@extends('layouts.app')

@section('title', $schedule->name)

@section('content')

<div style="max-width:1200px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
        <div>
            <h1 style="font-size:20px; font-weight:700; color:#1e1e2e;">{{ $schedule->name }}</h1>
            <p style="font-size:13px; color:#6c7086;">{{ $schedule->user->name }} · Slot: {{ $schedule->slot_duration }}min</p>
        </div>
        <div style="display:flex; gap:8px;">
            @can('share', $schedule)
                <a href="{{ route('schedules.share', $schedule) }}"
                   style="background:#f5f3ff; color:#7c3aed; padding:8px 16px; border-radius:8px;
                          text-decoration:none; font-size:13px; font-weight:500;">
                    👥 Compartilhar
                </a>
                <form method="POST" action="{{ route('schedules.booking-links.store', $schedule) }}" style="display:inline;">
                    @csrf
                    <button type="submit"
                            style="background:#eff6ff; color:#2563eb; padding:8px 16px; border-radius:8px;
                                   border:none; cursor:pointer; font-size:13px; font-weight:500;">
                        🔗 Gerar link público
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @if(session('link_url'))
        <div style="background:#d1fae5; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px;">
            ✅ Link gerado:
            <a href="{{ session('link_url') }}" target="_blank" style="color:#065f46; font-weight:600;">
                {{ session('link_url') }}
            </a>
        </div>
    @endif

    {{--
        ╔══════════════════════════════════════════════════════════════╗
        ║  MOUNT POINT DO REACT — Componente Calendar                  ║
        ║                                                              ║
        ║  Passamos via data-* os dados que o React precisa:          ║
        ║  - schedule-id: para chamadas de API                        ║
        ║  - can-edit: controla se botões de criação são visíveis     ║
        ║  - csrf-token: para requisições POST sem SPA                 ║
        ║                                                              ║
        ║  O React faz fetch de /api/schedules/{id}/appointments      ║
        ║  com o range visível do calendário.                          ║
        ╚══════════════════════════════════════════════════════════════╝
    --}}
    <div id="calendar-root"
         data-schedule-id="{{ $schedule->id }}"
         data-can-edit="{{ $canEdit ? 'true' : 'false' }}"
         data-csrf="{{ csrf_token() }}"
         style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
        {{-- Loading state enquanto React hidrata --}}
        <div style="display:flex; align-items:center; justify-content:center; height:400px; color:#9ca3af;">
            <div>⏳ Carregando calendário...</div>
        </div>
    </div>

</div>

@endsection

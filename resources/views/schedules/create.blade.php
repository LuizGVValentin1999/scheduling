@extends('layouts.app')

@section('title', 'Nova Agenda')

@section('content')

<div style="max-width:600px;">

    <h1 style="font-size:20px; font-weight:700; margin-bottom:24px; color:#1e1e2e;">Nova Agenda</h1>

    <form method="POST" action="{{ route('schedules.store') }}"
          style="background:#fff; border-radius:12px; padding:32px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
        @csrf

        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#374151;">Nome *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px;">
            @error('name') <span style="color:#ef4444; font-size:12px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#374151;">Descrição</label>
            <textarea name="description" rows="3"
                      style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; resize:vertical;">{{ old('description') }}</textarea>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#374151;">Duração padrão do slot (minutos)</label>
            <select name="slot_duration"
                    style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px;">
                @foreach([15, 30, 45, 60, 90, 120] as $min)
                    <option value="{{ $min }}" {{ old('slot_duration', 60) == $min ? 'selected' : '' }}>
                        {{ $min }}min
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="is_public" value="1" id="is_public"
                   {{ old('is_public') ? 'checked' : '' }}>
            <label for="is_public" style="font-size:14px; color:#374151;">Permitir agendamento público (via link)</label>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit"
                    style="background:#7c3aed; color:#fff; padding:12px 24px; border-radius:8px;
                           border:none; cursor:pointer; font-size:14px; font-weight:600;">
                Criar Agenda
            </button>
            <a href="{{ route('schedules.index') }}"
               style="background:#f3f4f6; color:#374151; padding:12px 24px; border-radius:8px;
                      text-decoration:none; font-size:14px; font-weight:600;">
                Cancelar
            </a>
        </div>

    </form>
</div>

@endsection

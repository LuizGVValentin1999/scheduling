@extends('layouts.app')

@section('title', 'Compartilhar agenda')

@section('content')

<div style="max-width:700px;">

    <h1 style="font-size:20px; font-weight:700; margin-bottom:4px; color:#1e1e2e;">
        Compartilhar: {{ $schedule->name }}
    </h1>
    <p style="color:#6c7086; font-size:13px; margin-bottom:24px;">
        Defina quem pode ver ou editar esta agenda dentro do workspace.
    </p>

    {{-- Adicionar novo compartilhamento --}}
    @if($tenantUsers->isNotEmpty())
        <div style="background:#fff; border-radius:12px; padding:24px; margin-bottom:16px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <h2 style="font-size:15px; font-weight:600; margin-bottom:16px;">Compartilhar com usuário</h2>
            <form method="POST" action="{{ route('schedules.shares.store', $schedule) }}"
                  style="display:flex; gap:10px; align-items:flex-end;">
                @csrf
                <div style="flex:1;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:#374151;">Usuário</label>
                    <select name="user_id" style="width:100%; padding:9px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px;">
                        @foreach($tenantUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:#374151;">Permissão</label>
                    <select name="permission" style="padding:9px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px;">
                        <option value="view">Visualizar</option>
                        <option value="edit">Editar</option>
                    </select>
                </div>
                <button type="submit"
                        style="background:#7c3aed; color:#fff; padding:9px 20px; border-radius:8px;
                               border:none; cursor:pointer; font-size:13px; font-weight:600; white-space:nowrap;">
                    + Adicionar
                </button>
            </form>
        </div>
    @endif

    {{-- Lista de compartilhamentos existentes --}}
    <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <h2 style="font-size:15px; font-weight:600; margin-bottom:16px;">Compartilhamentos ativos</h2>

        @forelse($shares as $share)
            <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f1f5f9;">
                @if($share->sharedWith->avatar)
                    <img src="{{ $share->sharedWith->avatar }}" style="width:32px; height:32px; border-radius:50%;">
                @else
                    <div style="width:32px; height:32px; border-radius:50%; background:#e5e7eb; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600;">
                        {{ strtoupper(substr($share->sharedWith->name, 0, 1)) }}
                    </div>
                @endif
                <div style="flex:1;">
                    <div style="font-size:14px; font-weight:500;">{{ $share->sharedWith->name }}</div>
                    <div style="font-size:12px; color:#6c7086;">{{ $share->sharedWith->email }}</div>
                </div>

                {{-- Atualizar permissão --}}
                <form method="POST" action="{{ route('schedules.shares.update', [$schedule, $share]) }}" style="display:flex; gap:6px;">
                    @csrf @method('PATCH')
                    <select name="permission" onchange="this.form.submit()"
                            style="padding:5px 8px; border:1px solid #e5e7eb; border-radius:6px; font-size:12px;
                                   background:{{ $share->permission === 'edit' ? '#fef3c7' : '#f0fdf4' }};
                                   color:{{ $share->permission === 'edit' ? '#92400e' : '#065f46' }};">
                        <option value="view" {{ $share->permission === 'view' ? 'selected' : '' }}>Visualizar</option>
                        <option value="edit" {{ $share->permission === 'edit' ? 'selected' : '' }}>Editar</option>
                    </select>
                </form>

                {{-- Remover --}}
                <form method="POST" action="{{ route('schedules.shares.destroy', [$schedule, $share]) }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Remover compartilhamento?')"
                            style="background:transparent; border:1px solid #fca5a5; color:#ef4444;
                                   padding:5px 10px; border-radius:6px; cursor:pointer; font-size:12px;">
                        Remover
                    </button>
                </form>
            </div>
        @empty
            <p style="color:#9ca3af; font-size:14px;">Esta agenda não foi compartilhada com ninguém ainda.</p>
        @endforelse
    </div>

    <a href="{{ route('schedules.show', $schedule) }}"
       style="display:inline-block; margin-top:16px; color:#6c7086; font-size:13px; text-decoration:none;">
        ← Voltar para a agenda
    </a>

</div>

@endsection

@extends('layouts.app')

@section('title', 'Usuários — Admin')

@section('content')

<div class="page-header">
    <div>
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-back">
                <span class="material-symbols-rounded">arrow_back</span>
            </a>
            <h1 class="page-title">Usuários do workspace</h1>
        </div>
        <div class="page-title-sub">{{ auth()->user()->currentTenant->name }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 300px 1fr; gap:20px; align-items:start;">

    {{-- Convidar --}}
    <div class="card">
        <h2 class="card-section-title">
            <span class="material-symbols-rounded">person_add</span>
            Convidar usuário
        </h2>
        <form method="POST" action="{{ route('admin.users.invite') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:#374151;">E-mail</label>
                <input type="email" name="email" required placeholder="usuario@email.com"
                       value="{{ old('email') }}"
                       style="width:100%; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:8px; font-size:13px; outline:none;">
                @error('email') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:#374151;">Papel</label>
                <select name="role" style="width:100%; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:8px; font-size:13px;">
                    <option value="member">Membro</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" style="width:100%; display:flex; align-items:center; justify-content:center; gap:6px; background:#7c3aed; color:#fff; padding:10px; border-radius:8px; border:none; cursor:pointer; font-size:13px; font-weight:600;">
                <span class="material-symbols-rounded" style="font-size:16px;">send</span>
                Convidar
            </button>
        </form>
    </div>

    {{-- Lista paginada --}}
    <div class="card">
        <h2 class="card-section-title">
            <span class="material-symbols-rounded">people</span>
            {{ $users->total() }} usuário{{ $users->total() !== 1 ? 's' : '' }}
        </h2>

        @foreach($users as $user)
        <div style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #f1f5f9;">
            <div style="width:38px; height:38px; border-radius:50%; overflow:hidden; background:linear-gradient(135deg,#7c3aed,#cba6f7); display:flex; align-items:center; justify-content:center; color:#fff; font-size:14px; font-weight:700; flex-shrink:0;">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:14px; font-weight:500; color:#1e1e2e; display:flex; align-items:center; gap:6px;">
                    {{ $user->name }}
                    @if($user->id === auth()->id())
                        <span style="background:#f5f3ff; color:#7c3aed; font-size:10px; font-weight:600; padding:2px 6px; border-radius:999px;">você</span>
                    @endif
                </div>
                <div style="font-size:12px; color:#6c7086; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $user->email }}</div>
            </div>

            <form method="POST" action="{{ route('admin.users.role', $user) }}">
                @csrf @method('PATCH')
                <select name="role" onchange="this.form.submit()"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}
                        style="padding:5px 8px; border-radius:6px; font-size:12px; font-weight:500; cursor:pointer; outline:none; border:1.5px solid transparent;
                               background:{{ $user->pivot->role === 'admin' ? '#fef3c7' : '#f0fdf4' }};
                               color:{{ $user->pivot->role === 'admin' ? '#92400e' : '#065f46' }};
                               border-color:{{ $user->pivot->role === 'admin' ? '#fde68a' : '#bbf7d0' }};">
                    <option value="member" {{ $user->pivot->role === 'member' ? 'selected' : '' }}>Membro</option>
                    <option value="admin"  {{ $user->pivot->role === 'admin'  ? 'selected' : '' }}>Admin</option>
                </select>
            </form>

            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Remover {{ addslashes($user->name) }} do workspace?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:transparent; border:1px solid #fca5a5; color:#ef4444; padding:5px 10px; border-radius:6px; cursor:pointer; font-size:12px; display:flex; align-items:center; gap:4px;">
                    <span class="material-symbols-rounded" style="font-size:14px;">delete</span>
                    Remover
                </button>
            </form>
            @endif
        </div>
        @endforeach

        @if($users->hasPages())
            <div style="margin-top:16px;">{{ $users->links() }}</div>
        @endif
    </div>

</div>

<style>
.breadcrumb-back {
    color: #6c7086; text-decoration: none; display: flex; align-items: center;
    padding: 4px; border-radius: 6px; transition: background .15s, color .15s;
}
.breadcrumb-back:hover { background: #f1f5f9; color: #1e1e2e; }
.breadcrumb-back .material-symbols-rounded { font-size: 20px; }
.card-section-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 15px; font-weight: 600; color: #1e1e2e; margin: 0 0 16px;
}
.card-section-title .material-symbols-rounded { font-size: 18px; color: #7c3aed; }
@media (max-width: 700px) {
    div[style*="grid-template-columns: 300px"] { grid-template-columns: 1fr !important; }
}
</style>

@endsection

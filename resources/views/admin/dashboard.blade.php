@extends('layouts.app')

@section('title', 'Admin — ' . $tenant->name)

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Painel Administrativo</h1>
        <div class="page-title-sub">{{ $tenant->name }}</div>
    </div>
    <a href="{{ route('admin.users') }}" class="btn-admin-action">
        <span class="material-symbols-rounded">group</span>
        Gerenciar usuários
    </a>
</div>

{{-- Stats --}}
<div class="admin-stats">
    @foreach([
        ['label' => 'Usuários',   'value' => $stats['total_users'],    'icon' => 'group',          'color' => '#3b82f6', 'bg' => '#eff6ff'],
        ['label' => 'Agendas',    'value' => $stats['total_schedules'],'icon' => 'calendar_month',  'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
        ['label' => 'Clientes',   'value' => $stats['total_clients'],  'icon' => 'person',          'color' => '#10b981', 'bg' => '#ecfdf5'],
    ] as $s)
    <div class="admin-stat-card">
        <div class="admin-stat-icon" style="background:{{ $s['bg'] }}; color:{{ $s['color'] }};">
            <span class="material-symbols-rounded" style="font-size:22px; font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;">{{ $s['icon'] }}</span>
        </div>
        <div>
            <div class="admin-stat-value" style="color:{{ $s['color'] }};">{{ $s['value'] }}</div>
            <div class="admin-stat-label">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="admin-grid">

    {{-- Convidar usuário --}}
    <div class="card">
        <h2 class="card-section-title">
            <span class="material-symbols-rounded">person_add</span>
            Convidar usuário
        </h2>
        <form method="POST" action="{{ route('admin.users.invite') }}" class="invite-form">
            @csrf
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">E-mail</label>
                <input type="email" name="email" required placeholder="usuario@email.com" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Papel</label>
                <select name="role" class="form-input">
                    <option value="member">Membro</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn-invite">
                <span class="material-symbols-rounded">send</span>
                Convidar
            </button>
        </form>
    </div>

    {{-- Lista de usuários --}}
    <div class="card" style="grid-column: span 2;">
        <h2 class="card-section-title">
            <span class="material-symbols-rounded">people</span>
            Usuários do workspace
        </h2>
        <div class="user-table">
            <div class="user-table-header">
                <span>Usuário</span>
                <span>Papel</span>
                <span>Ações</span>
            </div>
            @foreach($users as $user)
            <div class="user-table-row">
                <div class="user-cell">
                    <div class="user-avatar-sm">
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="user-name">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="badge-you">você</span>
                            @endif
                        </div>
                        <div class="user-email">{{ $user->email }}</div>
                    </div>
                </div>

                <div>
                    <form method="POST" action="{{ route('admin.users.role', $user) }}">
                        @csrf @method('PATCH')
                        <select name="role" onchange="this.form.submit()" class="role-select role-select--{{ $user->pivot->role }}"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <option value="member" {{ $user->pivot->role === 'member' ? 'selected' : '' }}>Membro</option>
                            <option value="admin"  {{ $user->pivot->role === 'admin'  ? 'selected' : '' }}>Admin</option>
                        </select>
                    </form>
                </div>

                <div>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Remover {{ $user->name }} do workspace?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-remove">
                            <span class="material-symbols-rounded">person_remove</span>
                            Remover
                        </button>
                    </form>
                    @else
                        <span style="font-size:12px; color:#9ca3af;">—</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

<style>
.btn-admin-action {
    display: inline-flex; align-items: center; gap: 6px;
    background: #7c3aed; color: #fff; padding: 10px 18px;
    border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;
    transition: background .15s;
}
.btn-admin-action:hover { background: #6d28d9; }
.btn-admin-action .material-symbols-rounded { font-size: 18px; }

.admin-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.admin-stat-card {
    background: #fff; border-radius: 12px; padding: 18px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    display: flex; align-items: center; gap: 14px;
}
.admin-stat-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.admin-stat-value { font-size: 26px; font-weight: 700; line-height: 1; }
.admin-stat-label { font-size: 12px; color: #6c7086; margin-top: 3px; }

.admin-grid {
    display: grid;
    grid-template-columns: 280px 1fr 1fr;
    gap: 20px;
    align-items: start;
}

.card-section-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 15px; font-weight: 600; color: #1e1e2e;
    margin: 0 0 16px;
}
.card-section-title .material-symbols-rounded { font-size: 18px; color: #7c3aed; }

.form-input {
    width: 100%; padding: 9px 12px; border: 1.5px solid #e5e7eb;
    border-radius: 8px; font-size: 13px; outline: none;
    transition: border-color .2s; background: #fff; font-family: inherit;
}
.form-input:focus { border-color: #7c3aed; }

.btn-invite {
    display: inline-flex; align-items: center; gap: 6px;
    background: #7c3aed; color: #fff; padding: 9px 18px;
    border-radius: 8px; border: none; cursor: pointer;
    font-size: 13px; font-weight: 600; transition: background .15s; width: 100%;
    justify-content: center;
}
.btn-invite:hover { background: #6d28d9; }
.btn-invite .material-symbols-rounded { font-size: 16px; }

.user-table { width: 100%; }
.user-table-header {
    display: grid; grid-template-columns: 1fr 120px 120px;
    padding: 8px 12px; font-size: 11px; font-weight: 700;
    color: #9ca3af; text-transform: uppercase; letter-spacing: .05em;
    border-bottom: 2px solid #f1f5f9;
}
.user-table-row {
    display: grid; grid-template-columns: 1fr 120px 120px;
    padding: 12px; align-items: center;
    border-bottom: 1px solid #f8fafc;
    transition: background .1s;
}
.user-table-row:last-child { border-bottom: none; }
.user-table-row:hover { background: #fafafa; }

.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar-sm {
    width: 34px; height: 34px; border-radius: 50%; overflow: hidden;
    background: linear-gradient(135deg, #7c3aed, #cba6f7);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.user-avatar-sm img { width: 100%; height: 100%; object-fit: cover; }
.user-name { font-size: 14px; font-weight: 500; color: #1e1e2e; display: flex; align-items: center; gap: 6px; }
.user-email { font-size: 12px; color: #6c7086; }
.badge-you {
    background: #f5f3ff; color: #7c3aed;
    font-size: 10px; font-weight: 600; padding: 2px 6px;
    border-radius: 999px;
}

.role-select {
    padding: 5px 8px; border-radius: 6px; border: 1.5px solid transparent;
    font-size: 12px; font-weight: 500; cursor: pointer; outline: none;
}
.role-select--admin  { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.role-select--member { background: #f0fdf4; color: #065f46; border-color: #bbf7d0; }
.role-select:disabled { opacity: .5; cursor: default; }

.btn-remove {
    display: inline-flex; align-items: center; gap: 4px;
    background: transparent; border: 1px solid #fca5a5; color: #ef4444;
    padding: 5px 10px; border-radius: 6px; cursor: pointer;
    font-size: 12px; font-weight: 500; transition: background .15s;
}
.btn-remove:hover { background: #fee2e2; }
.btn-remove .material-symbols-rounded { font-size: 14px; }

@media (max-width: 900px) {
    .admin-stats { grid-template-columns: 1fr 1fr; }
    .admin-grid  { grid-template-columns: 1fr; }
    .card[style*="span 2"] { grid-column: span 1; }
    .user-table-header, .user-table-row { grid-template-columns: 1fr 100px 100px; }
}
@media (max-width: 600px) {
    .admin-stats { grid-template-columns: 1fr; }
    .user-table-header { display: none; }
    .user-table-row { grid-template-columns: 1fr; gap: 8px; }
}
</style>

@endsection

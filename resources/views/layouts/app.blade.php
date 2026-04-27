<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Scheduling') — {{ auth()->user()?->currentTenant?->name ?? 'SaaS' }}</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>

{{-- Barra lateral fixa --}}
<aside id="sidebar" style="
    position:fixed; top:0; left:0; height:100vh; width:240px;
    background:#1e1e2e; color:#cdd6f4; display:flex; flex-direction:column;
    padding:16px 0; z-index:100;">

    {{-- Logo / Nome do workspace --}}
    <div style="padding:0 20px 16px; border-bottom:1px solid #313244;">
        <div style="font-weight:700; font-size:18px; color:#cba6f7;">📅 Scheduling</div>
        <div style="font-size:11px; color:#6c7086; margin-top:4px;">
            {{ auth()->user()?->currentTenant?->name }}
        </div>
    </div>

    {{-- Menu --}}
    <nav style="flex:1; padding:12px 0;">
        @php
            $navItems = [
                ['route' => 'dashboard',        'icon' => '🏠', 'label' => 'Dashboard'],
                ['route' => 'schedules.index',  'icon' => '📆', 'label' => 'Agendas'],
            ];
            if(auth()->user()?->isAdminOfCurrentTenant()) {
                $navItems[] = ['route' => 'admin.dashboard', 'icon' => '⚙️', 'label' => 'Admin'];
            }
        @endphp

        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               style="display:flex; align-items:center; gap:10px; padding:10px 20px;
                      color:{{ request()->routeIs($item['route'].'*') ? '#cba6f7' : '#cdd6f4' }};
                      background:{{ request()->routeIs($item['route'].'*') ? 'rgba(203,166,247,.15)' : 'transparent' }};
                      border-left:{{ request()->routeIs($item['route'].'*') ? '3px solid #cba6f7' : '3px solid transparent' }};
                      text-decoration:none; font-size:14px;">
                <span>{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Usuário + troca de workspace --}}
    <div style="padding:12px 20px; border-top:1px solid #313244;">
        @if(auth()->user()?->tenants()->count() > 1)
            <form action="" method="GET" style="margin-bottom:8px;">
                <select onchange="this.form.submit()" name="_switch_tenant"
                        style="width:100%; background:#313244; color:#cdd6f4; border:none; padding:4px 8px; border-radius:4px; font-size:12px;">
                    @foreach(auth()->user()->tenants as $t)
                        <option value="{{ $t->id }}"
                            {{ $t->id === auth()->user()->current_tenant_id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
            @if(auth()->user()?->avatar)
                <img src="{{ auth()->user()->avatar }}" style="width:28px; height:28px; border-radius:50%;">
            @endif
            <div>
                <div style="font-size:12px; font-weight:600;">{{ auth()->user()?->name }}</div>
                <div style="font-size:10px; color:#6c7086;">{{ auth()->user()?->roleInCurrentTenant() }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    style="width:100%; background:transparent; border:1px solid #45475a;
                           color:#f38ba8; padding:6px; border-radius:4px; cursor:pointer; font-size:12px;">
                Sair
            </button>
        </form>
    </div>
</aside>

{{-- Conteúdo principal --}}
<main style="margin-left:240px; min-height:100vh; background:#f8fafc; padding:24px;">

    {{-- Flash messages --}}
    @foreach(['success' => '#d1fae5', 'error' => '#fee2e2', 'info' => '#dbeafe'] as $type => $bg)
        @if(session($type))
            <div style="background:{{ $bg }}; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;">
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    @yield('content')
</main>

</body>
</html>

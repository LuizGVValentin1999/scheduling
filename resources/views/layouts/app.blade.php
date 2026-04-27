<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Scheduling') — {{ auth()->user()?->currentTenant?->name ?? 'SaaS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>

{{-- Overlay para fechar sidebar no mobile --}}
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

{{-- Topbar mobile --}}
<header class="topbar">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
        <span class="material-symbols-rounded">menu</span>
    </button>
    <div class="topbar-brand">
        <span class="material-symbols-rounded" style="color:#cba6f7;">calendar_month</span>
        <span>Scheduling</span>
    </div>
    <div class="topbar-avatar">
        @if(auth()->user()?->avatar)
            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
        @else
            <div class="avatar-fallback">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}</div>
        @endif
    </div>
</header>

{{-- Barra lateral --}}
<aside id="sidebar">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <span class="material-symbols-rounded">calendar_month</span>
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">Scheduling</div>
            <div class="sidebar-brand-workspace">{{ auth()->user()?->currentTenant?->name }}</div>
        </div>
        <button class="sidebar-close" onclick="closeSidebar()" aria-label="Fechar menu">
            <span class="material-symbols-rounded">close</span>
        </button>
    </div>

    {{-- Navegação --}}
    <nav class="sidebar-nav">
        @php
            $navItems = [
                ['route' => 'dashboard',       'icon' => 'home',          'label' => 'Dashboard'],
                ['route' => 'schedules.index', 'icon' => 'calendar_month','label' => 'Agendas'],
            ];
            if(auth()->user()?->isAdminOfCurrentTenant()) {
                $navItems[] = ['route' => 'admin.dashboard', 'icon' => 'admin_panel_settings', 'label' => 'Admin'];
            }
        @endphp

        @foreach($navItems as $item)
            @php $active = request()->routeIs($item['route'].'*'); @endphp
            <a href="{{ route($item['route']) }}"
               class="nav-item {{ $active ? 'nav-item--active' : '' }}"
               onclick="closeSidebar()">
                <span class="material-symbols-rounded nav-item-icon">{{ $item['icon'] }}</span>
                <span class="nav-item-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Troca de workspace --}}
    @if(auth()->user()?->tenants()->count() > 1)
        <div class="sidebar-workspaces">
            <div class="sidebar-section-label">
                <span class="material-symbols-rounded">business</span>
                Workspace
            </div>
            <form id="workspace-switch-form" action="" method="GET">
                <select onchange="
                    var tid = this.value;
                    fetch('{{ url('/workspaces') }}/' + tid + '/switch', {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json'}
                    }).then(() => location.reload());
                " class="workspace-select">
                    @foreach(auth()->user()->tenants as $t)
                        <option value="{{ $t->id }}"
                            {{ $t->id === auth()->user()->current_tenant_id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    {{-- Rodapé: usuário + logout --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                @if(auth()->user()?->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                @else
                    <div class="avatar-fallback">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}</div>
                @endif
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ auth()->user()?->name }}</div>
                <div class="sidebar-user-role">{{ auth()->user()?->roleInCurrentTenant() }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <span class="material-symbols-rounded">logout</span>
                Sair
            </button>
        </form>
    </div>
</aside>

{{-- Conteúdo principal --}}
<main class="main-content">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flash flash--success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash flash--error">
            <span class="material-symbols-rounded">error</span>
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="flash flash--info">
            <span class="material-symbols-rounded">info</span>
            {{ session('info') }}
        </div>
    @endif

    @yield('content')
</main>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('sidebar--open');
    overlay.classList.toggle('overlay--visible');
    document.body.classList.toggle('sidebar-open');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.remove('sidebar--open');
    overlay.classList.remove('overlay--visible');
    document.body.classList.remove('sidebar-open');
}
</script>

</body>
</html>

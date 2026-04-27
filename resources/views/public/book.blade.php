<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $link->label }} — {{ $schedule->name }}</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @php
        $settings = $link->settings ?? [];
        $primary  = $settings['primary_color'] ?? '#7c3aed';
    @endphp
    <style>
        body { background: #f8fafc; display: flex; flex-direction: column; min-height: 100vh; font-family: system-ui, sans-serif; }
        .page-header { background: {{ $primary }}; color: white; padding: 20px 24px; text-align: center; }
        .container { max-width: 520px; margin: 32px auto; padding: 0 16px; }
    </style>
</head>
<body>

<div class="page-header">
    <h1 style="font-size:20px; font-weight:700;">{{ $link->label }}</h1>
    <p style="font-size:13px; opacity:.85;">{{ $schedule->name }} · {{ $schedule->user->name }}</p>
</div>

<div class="container">
    @if(session('success'))
        <div style="background:#d1fae5; color:#065f46; padding:14px 16px; border-radius:10px; margin-bottom:20px; font-size:14px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{--
        Formulário Blade + React híbrido:
        - Se JS habilitado → React monta o widget de 4 steps
        - Se JS desabilitado → formulário Blade simples funciona

        O mount point id="booking-widget-root" é detectado pelo app.jsx
        Mas aqui usamos o widget standalone que lê data-token diretamente.
    --}}
    <div id="scheduling-widget"
         data-token="{{ $link->token }}"
         data-base-url="{{ url('') }}"
         data-settings='{{ json_encode($settings) }}'>

        {{-- Fallback sem JS --}}
        <noscript>
            <form method="POST" action="{{ route('public.book.store', $link->token) }}"
                  style="background:#fff; padding:24px; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                @csrf
                <h2 style="font-size:16px; margin-bottom:16px;">Preencha seus dados</h2>
                @foreach(['client_name' => 'Nome *', 'client_email' => 'E-mail *', 'client_phone' => 'Telefone'] as $field => $label)
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">{{ $label }}</label>
                        <input type="{{ $field === 'client_email' ? 'email' : 'text' }}"
                               name="{{ $field }}"
                               style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px;"
                               {{ in_array($field, ['client_name','client_email']) ? 'required' : '' }}>
                    </div>
                @endforeach
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Data/hora início *</label>
                        <input type="datetime-local" name="starts_at" required
                               style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Data/hora término *</label>
                        <input type="datetime-local" name="ends_at" required
                               style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px;">
                    </div>
                </div>
                <button type="submit" style="background:{{ $primary }}; color:#fff; padding:12px 24px; border-radius:8px; border:none; cursor:pointer; font-size:14px; font-weight:600; width:100%;">
                    Confirmar agendamento
                </button>
            </form>
        </noscript>
    </div>
</div>

{{-- Script do widget standalone (gerado por `npm run widget:build`) --}}
<script>
    // O widget React auto-inicializa ao carregar o script
    // O app.jsx principal NÃO monta este widget — ele tem seu próprio bundle
</script>
<script src="{{ asset('widget/scheduling-widget.iife.js') }}" defer></script>

</body>
</html>

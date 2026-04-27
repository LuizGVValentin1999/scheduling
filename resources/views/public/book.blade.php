<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $link->label ?? 'Agendar' }} — {{ $schedule->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @php
        $settings     = $link->settings ?? [];
        $primary      = $settings['primary_color'] ?? '#7c3aed';
        $providerName = $schedule->user->name;
        $initials     = collect(explode(' ', $providerName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
    @endphp
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }

        /* ---- Top bar ---- */
        .pb-topbar {
            background: {{ $primary }};
            padding: 0;
            position: relative;
            overflow: hidden;
        }
        .pb-topbar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .pb-topbar-inner {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 24px 72px;
            position: relative;
            z-index: 1;
        }
        .pb-provider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }
        .pb-avatar {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: rgba(255,255,255,.25);
            border: 2px solid rgba(255,255,255,.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; color: #fff;
            flex-shrink: 0;
            overflow: hidden;
        }
        .pb-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .pb-provider-name { font-size: 14px; font-weight: 600; color: rgba(255,255,255,.95); }
        .pb-provider-role { font-size: 12px; color: rgba(255,255,255,.7); margin-top: 2px; }
        .pb-title {
            font-size: 28px; font-weight: 700; color: #fff;
            margin: 0 0 8px;
            line-height: 1.2;
        }
        .pb-subtitle {
            font-size: 15px; color: rgba(255,255,255,.8);
            margin: 0;
        }

        /* ---- Info chips ---- */
        .pb-chips {
            display: flex; flex-wrap: wrap; gap: 10px;
            margin-top: 20px;
        }
        .pb-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px; font-weight: 500;
        }
        .pb-chip .material-symbols-rounded { font-size: 15px; }

        /* ---- Main content ---- */
        .pb-main {
            max-width: 960px;
            margin: -48px auto 0;
            padding: 0 24px 48px;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 24px;
            align-items: start;
        }

        /* ---- Info card (esquerda) ---- */
        .pb-info-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }
        .pb-info-section { margin-bottom: 24px; }
        .pb-info-section:last-child { margin-bottom: 0; }
        .pb-info-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 11px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .06em;
            margin-bottom: 10px;
        }
        .pb-info-label .material-symbols-rounded {
            font-size: 16px;
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            color: {{ $primary }};
        }
        .pb-info-value { font-size: 14px; color: #334155; line-height: 1.6; }

        .pb-step {
            display: flex; gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .pb-step:last-child { border-bottom: none; }
        .pb-step-num {
            width: 26px; height: 26px;
            border-radius: 50%;
            background: {{ $primary }}1a;
            color: {{ $primary }};
            font-size: 12px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
        }
        .pb-step-text { font-size: 13px; color: #475569; }
        .pb-step-text strong { color: #1e293b; display: block; font-size: 14px; margin-bottom: 2px; }

        /* ---- Widget card (direita) ---- */
        .pb-widget-wrap {
            background: #fff;
            border-radius: 16px;
            padding: 8px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            position: sticky;
            top: 24px;
        }

        /* ---- Flash de sucesso ---- */
        .pb-flash {
            background: #d1fae5; color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 12px;
            font-size: 14px;
            grid-column: 1 / -1;
        }
        .pb-flash .material-symbols-rounded {
            font-size: 22px; color: #059669; flex-shrink: 0;
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        /* ---- Footer ---- */
        .pb-footer {
            text-align: center;
            padding: 16px 24px 32px;
            font-size: 12px; color: #94a3b8;
        }
        .pb-footer a { color: #94a3b8; text-decoration: none; }
        .pb-footer a:hover { color: #64748b; text-decoration: underline; }

        /* ---- Responsive ---- */
        @media (max-width: 720px) {
            .pb-topbar-inner { padding: 24px 16px 60px; }
            .pb-title { font-size: 22px; }
            .pb-main {
                grid-template-columns: 1fr;
                padding: 0 16px 32px;
                margin-top: -40px;
            }
            .pb-widget-wrap { position: static; }
            .pb-info-card { order: 2; }
            .pb-widget-wrap { order: 1; }
        }
    </style>
</head>
<body>

{{-- Top bar colorida --}}
<div class="pb-topbar">
    <div class="pb-topbar-inner">
        <div class="pb-provider">
            <div class="pb-avatar">
                @if($schedule->user->avatar)
                    <img src="{{ $schedule->user->avatar }}" alt="{{ $providerName }}">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div>
                <div class="pb-provider-name">{{ $providerName }}</div>
                <div class="pb-provider-role">{{ $schedule->name }}</div>
            </div>
        </div>

        <h1 class="pb-title">{{ $link->label ?? 'Agende um horário' }}</h1>
        @if($schedule->description)
            <p class="pb-subtitle">{{ $schedule->description }}</p>
        @endif

        <div class="pb-chips">
            <div class="pb-chip">
                <span class="material-symbols-rounded">schedule</span>
                {{ $schedule->slot_duration }} minutos por sessão
            </div>
            <div class="pb-chip">
                <span class="material-symbols-rounded">event_available</span>
                Confirmação imediata
            </div>
            <div class="pb-chip">
                <span class="material-symbols-rounded">lock</span>
                Agendamento seguro
            </div>
        </div>
    </div>
</div>

{{-- Conteúdo principal --}}
<div class="pb-main">

    @if(session('success'))
        <div class="pb-flash">
            <span class="material-symbols-rounded">check_circle</span>
            <div>
                <strong>Agendamento realizado!</strong><br>
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Info card --}}
    <div class="pb-info-card">
        <div class="pb-info-section">
            <div class="pb-info-label">
                <span class="material-symbols-rounded">info</span>
                Como funciona
            </div>
            <div class="pb-step">
                <div class="pb-step-num">1</div>
                <div class="pb-step-text">
                    <strong>Escolha o dia</strong>
                    Selecione uma data disponível no calendário
                </div>
            </div>
            <div class="pb-step">
                <div class="pb-step-num">2</div>
                <div class="pb-step-text">
                    <strong>Escolha o horário</strong>
                    Veja os slots disponíveis e clique no horário desejado
                </div>
            </div>
            <div class="pb-step">
                <div class="pb-step-num">3</div>
                <div class="pb-step-text">
                    <strong>Preencha seus dados</strong>
                    Nome e pelo menos um contato (e-mail ou telefone)
                </div>
            </div>
            <div class="pb-step">
                <div class="pb-step-num">4</div>
                <div class="pb-step-text">
                    <strong>Pronto!</strong>
                    Seu agendamento é confirmado na hora
                </div>
            </div>
        </div>

        <div class="pb-info-section">
            <div class="pb-info-label">
                <span class="material-symbols-rounded">person</span>
                Profissional
            </div>
            <div class="pb-info-value">
                <strong>{{ $providerName }}</strong><br>
                {{ $schedule->name }}
            </div>
        </div>

        <div class="pb-info-section">
            <div class="pb-info-label">
                <span class="material-symbols-rounded">schedule</span>
                Duração
            </div>
            <div class="pb-info-value">{{ $schedule->slot_duration }} minutos</div>
        </div>

        @if(!empty($schedule->working_hours))
            <div class="pb-info-section">
                <div class="pb-info-label">
                    <span class="material-symbols-rounded">calendar_today</span>
                    Horários de atendimento
                </div>
                @php
                    $days = ['mon'=>'Seg','tue'=>'Ter','wed'=>'Qua','thu'=>'Qui','fri'=>'Sex','sat'=>'Sáb','sun'=>'Dom'];
                @endphp
                @foreach($days as $key => $label)
                    @if(!empty($schedule->working_hours[$key]['active']))
                        <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569; padding:4px 0; border-bottom:1px solid #f8fafc;">
                            <span>{{ $label }}</span>
                            <span style="font-weight:500; color:#1e293b;">{{ $schedule->working_hours[$key]['start'] }} – {{ $schedule->working_hours[$key]['end'] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Widget de agendamento --}}
    <div class="pb-widget-wrap">
        <div id="scheduling-widget"
             data-token="{{ $link->token }}"
             data-base-url="{{ url('') }}"
             data-settings='{{ json_encode($settings) }}'>

            {{-- Fallback sem JS --}}
            <noscript>
                <form method="POST" action="{{ route('public.book.store', $link->token) }}"
                      style="padding:24px;">
                    @csrf
                    @foreach(['client_name' => 'Nome *', 'client_email' => 'E-mail', 'client_phone' => 'Telefone / WhatsApp'] as $field => $label)
                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:#374151;">{{ $label }}</label>
                            <input type="{{ $field === 'client_email' ? 'email' : 'text' }}" name="{{ $field }}"
                                   style="width:100%; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px;"
                                   {{ $field === 'client_name' ? 'required' : '' }}>
                        </div>
                    @endforeach
                    <p style="font-size:12px; color:#94a3b8; margin-bottom:14px;">* Informe e-mail ou telefone</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:#374151;">Início *</label>
                            <input type="datetime-local" name="starts_at" required style="width:100%; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:#374151;">Término *</label>
                            <input type="datetime-local" name="ends_at" required style="width:100%; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px;">
                        </div>
                    </div>
                    <button type="submit" style="width:100%; background:{{ $primary }}; color:#fff; padding:12px; border-radius:10px; border:none; cursor:pointer; font-size:14px; font-weight:600;">
                        Confirmar agendamento
                    </button>
                </form>
            </noscript>
        </div>
    </div>

</div>

<div class="pb-footer">
    Powered by <strong>Scheduling</strong>
    &nbsp;·&nbsp;
    <a href="{{ url('/login') }}">Área restrita</a>
</div>

<script src="{{ asset('widget/scheduling-widget.iife.js') }}" defer></script>

</body>
</html>

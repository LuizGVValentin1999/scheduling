<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Scheduling SaaS</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e1e2e 0%, #313244 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }
        .logo { font-size: 36px; text-align:center; margin-bottom: 6px; }
        h1 { font-size: 22px; color: #1e1e2e; margin-bottom: 2px; text-align:center; }
        .subtitle { color: #6c7086; font-size: 13px; margin-bottom: 24px; text-align:center; }
        .btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 12px; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            text-decoration: none; margin-bottom: 10px; transition: opacity .2s;
        }
        .btn:hover { opacity: .85; }
        .btn-google    { background: #fff; color: #333; border: 2px solid #e5e7eb; }
        .btn-microsoft { background: #0078d4; color: #fff; }
        .btn-primary   { background: #7c3aed; color: #fff; margin-top: 4px; }
        .divider {
            margin: 20px 0; border: none; border-top: 1px solid #e5e7eb; position: relative;
        }
        .divider::after {
            content: attr(data-label);
            position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
            background: #fff; padding: 0 10px; color: #9ca3af; font-size: 12px;
        }
        label  { display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:#374151; }
        input[type=email], input[type=password] {
            width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;
            font-size:14px; margin-bottom:12px; outline:none; transition: border .2s;
        }
        input:focus { border-color:#7c3aed; }
        .error { background:#fee2e2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:14px; font-size:13px; }
        .remember { display:flex; align-items:center; gap:6px; font-size:13px; color:#374151; margin-bottom:4px; }
        .footer-link { text-align:center; margin-top:20px; font-size:13px; color:#6c7086; }
        .footer-link a { color:#7c3aed; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">📅</div>
    <h1>Bem-vindo</h1>
    <p class="subtitle">Sistema de agendamentos SaaS</p>

    {{-- Erros --}}
    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif
    @if($errors->has('email'))
        <div class="error">{{ $errors->first('email') }}</div>
    @endif

    {{-- Social Login --}}
    <a href="{{ route('auth.google') }}" class="btn btn-google">
        <svg width="18" height="18" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continuar com Google
    </a>
    <a href="{{ route('auth.microsoft') }}" class="btn btn-microsoft">
        <svg width="18" height="18" viewBox="0 0 23 23" fill="none">
            <rect width="11" height="11" fill="#F25022"/><rect x="12" width="11" height="11" fill="#7FBA00"/>
            <rect y="12" width="11" height="11" fill="#00A4EF"/><rect x="12" y="12" width="11" height="11" fill="#FFB900"/>
        </svg>
        Continuar com Microsoft
    </a>

    <hr class="divider" data-label="ou entre com e-mail">

    {{-- Formulário e-mail/senha --}}
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email"
               value="{{ old('email') }}" required autofocus
               placeholder="seu@email.com">

        <label for="password">Senha</label>
        <input type="password" id="password" name="password"
               required placeholder="••••••••">

        <label class="remember">
            <input type="checkbox" name="remember" style="width:auto;margin:0;">
            Lembrar de mim
        </label>

        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <div class="footer-link">
        Não tem conta? <a href="{{ route('register') }}">Criar conta grátis</a>
    </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta — Scheduling SaaS</title>
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
            padding: 24px 0;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }
        .logo { font-size: 32px; text-align:center; margin-bottom: 6px; }
        h1 { font-size: 20px; color: #1e1e2e; text-align:center; margin-bottom: 4px; }
        .subtitle { color: #6c7086; font-size: 13px; text-align:center; margin-bottom: 24px; }
        label  { display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:#374151; }
        input[type=text], input[type=email], input[type=password] {
            width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;
            font-size:14px; margin-bottom:4px; outline:none; transition: border .2s;
        }
        input:focus { border-color:#7c3aed; }
        .field { margin-bottom:14px; }
        .error-msg { color:#ef4444; font-size:11px; margin-top:2px; }
        .btn-primary {
            width:100%; padding:12px; background:#7c3aed; color:#fff; border:none;
            border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;
            margin-top:4px; transition: opacity .2s;
        }
        .btn-primary:hover { opacity:.88; }
        .section-title {
            font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase;
            letter-spacing:.05em; margin: 20px 0 14px; border-top:1px solid #f1f5f9;
            padding-top:16px;
        }
        .footer-link { text-align:center; margin-top:20px; font-size:13px; color:#6c7086; }
        .footer-link a { color:#7c3aed; text-decoration:none; font-weight:600; }
        .alert-error {
            background:#fee2e2; color:#b91c1c; padding:10px 14px;
            border-radius:8px; margin-bottom:16px; font-size:13px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">📅</div>
    <h1>Criar conta</h1>
    <p class="subtitle">Comece a usar o Scheduling gratuitamente</p>

    @if($errors->any())
        <div class="alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        {{-- Dados pessoais --}}
        <div class="field">
            <label for="name">Seu nome *</label>
            <input type="text" id="name" name="name"
                   value="{{ old('name') }}" required placeholder="João Silva">
            @error('name') <div class="error-msg">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}" required placeholder="joao@email.com">
            @error('email') <div class="error-msg">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password">Senha * <span style="font-weight:400;color:#9ca3af;">(mínimo 8 caracteres)</span></label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
            @error('password') <div class="error-msg">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmar senha *</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   required placeholder="••••••••">
        </div>

        {{-- Workspace --}}
        <div class="section-title">Seu workspace</div>

        <div class="field">
            <label for="workspace_name">Nome do workspace *</label>
            <input type="text" id="workspace_name" name="workspace_name"
                   value="{{ old('workspace_name') }}" required
                   placeholder="Ex: Barbearia do João, Clínica X...">
            @error('workspace_name') <div class="error-msg">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-primary">Criar conta</button>
    </form>

    <div class="footer-link">
        Já tem conta? <a href="{{ route('login') }}">Entrar</a>
    </div>
</div>

</body>
</html>

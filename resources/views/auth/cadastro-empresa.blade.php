<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('adminlte.title', 'Sistema') }} — Começar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: #f0f2f6;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.25rem 1rem;
        }
        .wrap { width: 100%; max-width: 640px; }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 0 0 1px rgba(0,0,0,.05), 0 4px 6px rgba(0,0,0,.04), 0 16px 48px rgba(0,0,0,.09);
            overflow: hidden;
        }
        .head { padding: 1.6rem 1.8rem 1.25rem; border-bottom: 1px solid #eef2f7; }
        .title { font-size: 1.15rem; font-weight: 800; letter-spacing: -.02em; margin-bottom: .35rem; }
        .sub { font-size: .85rem; color: #64748b; line-height: 1.5; }
        .body { padding: 1.6rem 1.8rem 1.8rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
        label { font-size: .78rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: block; }
        input {
            width: 100%;
            height: 42px;
            padding: 0 .85rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: .9rem;
            outline: none;
            background: #fff;
        }
        input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .row { margin-bottom: 12px; }
        .req { color: #ef4444; margin-left: 3px; }
        .alert {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: .82rem;
            margin-bottom: 14px;
        }
        .actions { display: flex; justify-content: space-between; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .btn {
            height: 44px;
            border-radius: 12px;
            padding: 0 1rem;
            font-weight: 800;
            font-size: .86rem;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .15s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 12px 24px rgba(37,99,235,.18);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(37,99,235,.22); }
        .btn-ghost { background: #fff; border-color: #e5e7eb; color: #111827; }
        .btn-ghost:hover { border-color: #cbd5e1; }
        .note {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            line-height: 1.55;
        }
        .note strong { color: #0f172a; }
    </style>
</head>

<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <div class="title">Começar agora</div>
            <div class="sub">
                Preencha os dados principais da sua empresa. Ao final, o sistema cria um usuario unico com senha padrao
                (no primeiro login voce sera obrigado a definir uma nova senha).
            </div>
        </div>

        <div class="body">
            @if ($errors->any())
                <div class="alert">
                    <strong>Corrija os campos abaixo:</strong>
                    <ul style="margin:.4rem 0 0 1.1rem;">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('cadastro.empresa.store') }}">
                @csrf

                <div class="row">
                    <label>Empresa <span class="req">*</span></label>
                    <input name="empresa_nome" value="{{ old('empresa_nome') }}" placeholder="Nome da locadora" required>
                </div>

                <div class="grid">
                    <div class="row">
                        <label>CNPJ</label>
                        <input name="empresa_cnpj" value="{{ old('empresa_cnpj') }}" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="row">
                        <label>Telefone</label>
                        <input name="empresa_telefone" value="{{ old('empresa_telefone') }}" placeholder="(00) 00000-0000">
                    </div>
                </div>

                <div class="row">
                    <label>E-mail da empresa</label>
                    <input name="empresa_email" type="email" value="{{ old('empresa_email') }}" placeholder="financeiro@empresa.com">
                </div>

                <hr style="border:0;border-top:1px solid #eef2f7;margin:14px 0;">

                <div class="grid">
                    <div class="row">
                        <label>Responsavel <span class="req">*</span></label>
                        <input name="admin_nome" value="{{ old('admin_nome') }}" placeholder="Nome do responsavel" required>
                    </div>
                    <div class="row">
                        <label>E-mail do responsavel <span class="req">*</span></label>
                        <input name="admin_email" type="email" value="{{ old('admin_email') }}" placeholder="voce@empresa.com" required>
                    </div>
                </div>

                <input type="hidden" name="plano" value="{{ old('plano', $planoPreferido ?? '') }}">
                <input type="hidden" name="sem_site" value="{{ old('sem_site', !empty($semSite) ? '1' : '0') }}">

                <div class="note">
                    <strong>Importante:</strong> apos o cadastro, voce conseguira acessar o painel, mas os modulos ficam bloqueados
                    ate o pagamento do plano ser confirmado.
                </div>

                <div class="actions">
                    <a class="btn btn-ghost" href="{{ route('site.apresentacao') }}">Voltar</a>
                    <button type="submit" class="btn btn-primary">Criar acesso</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>


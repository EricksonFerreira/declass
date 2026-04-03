<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva confirmada | {{ $empresa->nome }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(140deg, {{ $empresa->cor_primaria ?: '#2F6F3F' }}, {{ $empresa->cor_secundaria ?: '#193C68' }});
            color: #fff;
            display: grid;
            place-items: center;
            padding: 18px;
        }

        .card {
            width: min(720px, 100%);
            background: rgba(3, 7, 18, 0.3);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            padding: 24px;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.25);
        }

        .header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: contain;
            background: #fff;
            padding: 4px;
        }

        h1 { margin: 0 0 8px; font-size: 1.65rem; }
        p { margin: 0 0 14px; color: rgba(255, 255, 255, .9); }

        .details {
            background: rgba(255, 255, 255, .12);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .25);
            padding: 12px;
            margin-bottom: 16px;
        }

        .row {
            padding: 6px 0;
            font-size: .92rem;
            border-bottom: 1px dashed rgba(255, 255, 255, .28);
        }

        .row:last-child { border-bottom: 0; }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            border-radius: 999px;
            padding: 10px 14px;
            font-weight: 700;
            text-decoration: none;
            font-size: .86rem;
        }

        .btn-primary {
            background: #ffffff;
            color: #0f172a;
        }

        .btn-secondary {
            border: 1px solid rgba(255, 255, 255, .38);
            color: #fff;
        }
    </style>
</head>
<body>
    <section class="card">
        <div class="header">
            @if(!empty($empresa->logo_url))
                <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="logo">
            @endif
            <strong>{{ $empresa->nome }}</strong>
        </div>

        <h1>Reserva recebida com sucesso</h1>
        <p>Seu atendimento foi registrado. Para seguir com o pagamento, faca login na area administrativa.</p>

        <div class="details">
            <div class="row"><strong>Protocolo:</strong> #{{ $reserva->id }}</div>
            <div class="row"><strong>Cliente:</strong> {{ $reserva->usuario?->nome ?? 'Nao informado' }}</div>
            <div class="row"><strong>Retirada:</strong> {{ $reserva->estacaoRetirada?->nome ?? '-' }} em {{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</div>
            <div class="row"><strong>Devolucao:</strong> {{ $reserva->estacaoDevolucao?->nome ?? '-' }} em {{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</div>
            <div class="row"><strong>Modelo:</strong> {{ $reserva->modelos?->nome ?? '-' }}</div>
        </div>

        <div class="actions">
            <a href="{{ $urlNovaReserva }}" class="btn btn-primary">Nova reserva</a>
            <a href="{{ route('site.apresentacao') }}" class="btn btn-secondary">Voltar ao site</a>
            @if(!empty($urlPagamento))
                @if($isAuth)
                    <a href="{{ $urlPagamento }}" class="btn btn-secondary">Ir para pagamento</a>
                @else
                    <a href="{{ $loginUrl }}" class="btn btn-secondary">Fazer login para pagar</a>
                @endif
            @endif
            <a href="{{ $urlAdmin }}" class="btn btn-secondary">Area administrativa</a>
        </div>
    </section>
</body>
</html>

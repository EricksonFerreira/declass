@php
    $corPrimaria = $empresa->cor_primaria ?: '#1f4f7a';
    $corSecundaria = $empresa->cor_secundaria ?: '#14324d';
    $moeda = strtoupper((string) ($moedaPadrao ?? 'BRL'));
    $simbolo = $moeda === 'USD' ? '$' : ($moeda === 'EUR' ? 'EUR' : 'R$');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento | {{ $empresa->nome }}</title>
    <style>
        :root { --p: {{ $corPrimaria }}; --s: {{ $corSecundaria }}; --line: #dbe4ee; --ink: #0f172a; --muted: #64748b; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: var(--ink); background: #f4f8fc; }
        .container { width: min(1060px, calc(100% - 24px)); margin: 0 auto; }
        .top { background: #fff; border-bottom: 1px solid var(--line); }
        .top .inner { min-height: 70px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; }
        .brand img { width: 42px; height: 42px; border-radius: 10px; object-fit: contain; background: var(--p); border: 1px solid #e2e8f0; padding: 4px; }
        .back { text-decoration: none; border: 1px solid var(--line); border-radius: 999px; padding: 10px 14px; color: #334155; font-size: 13px; font-weight: 700; background: #fff; }
        .layout { display: grid; grid-template-columns: 1fr 320px; gap: 14px; padding: 16px 0 24px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 10px 22px rgba(15, 23, 42, .06); }
        .head { padding: 18px; color: #fff; background: linear-gradient(120deg, var(--p), var(--s)); border-radius: 14px 14px 0 0; }
        .head h1 { margin: 0 0 6px; font-size: 22px; }
        .head p { margin: 0; font-size: 13px; opacity: .92; }
        .body { padding: 16px 18px; }
        .title { margin: 0 0 10px; font-size: 16px; }
        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px; }
        label { font-size: 12px; color: #334155; font-weight: 700; }
        select { min-height: 40px; border: 1px solid var(--line); border-radius: 9px; padding: 8px 10px; font-size: 14px; background: #f8fbff; color: var(--ink); }
        select:focus { outline: none; border-color: var(--p); box-shadow: 0 0 0 2px rgba(37, 99, 235, .12); background: #fff; }
        .btn { min-height: 40px; border: 0; border-radius: 999px; padding: 9px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: var(--p); color: #fff; }
        .alert { border-radius: 10px; padding: 10px 12px; font-size: 14px; margin-bottom: 10px; }
        .ok { border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; }
        .err { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
        .summary { padding: 14px; position: sticky; top: 85px; }
        .summary h3 { margin: 0 0 10px; font-size: 16px; }
        .sum { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; padding: 7px 0; border-bottom: 1px dashed var(--line); }
        .sum.total { border-bottom: 0; font-weight: 800; font-size: 16px; margin-top: 6px; }
        @media (max-width: 980px) { .layout { grid-template-columns: 1fr; } .summary { position: static; } }
    </style>
</head>
<body>
    <header class="top">
        <div class="container inner">
            <a class="brand" href="{{ route('site.apresentacao') }}">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}">
                @endif
                <span>{{ $empresa->nome }}</span>
            </a>
            <a class="back" href="{{ $urlVoltar }}">Voltar</a>
        </div>
    </header>

    <main class="container layout">
        <section class="card">
            <div class="head">
                <h1>Pagamento da reserva #{{ $reserva->id }}</h1>
                <p>Finalize o pagamento para confirmar sua reserva.</p>
            </div>

            <div class="body">
                @if(session('success'))
                    <div class="alert ok">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert err">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ $urlProcessar }}">
                    @csrf
                    <div class="field">
                        <label>Gateway configurado</label>
                        <input type="text" value="{{ $gatewayNome ?? strtoupper($gatewayPadrao ?? 'stripe') }}" readonly
                            style="min-height:40px;border:1px solid var(--line);border-radius:9px;padding:8px 10px;background:#f8fbff;">
                        <small style="font-size:12px;color:var(--muted);">Moeda: {{ $moeda }}</small>
                    </div>

                    @if(($gatewayPadrao ?? 'stripe') === 'stripe')
                        <div class="field">
                            <label for="payment_method">Metodo de pagamento (Stripe)</label>
                            <select id="payment_method" name="payment_method" {{ count($metodosPagamento) ? 'required' : '' }}>
                                @if(count($metodosPagamento) == 0)
                                    <option value="" disabled selected>Nenhum metodo de pagamento disponivel</option>
                                @else
                                    @foreach($metodosPagamento as $metodo)
                                        <option value="{{ $metodo['id'] }}">
                                            {{ strtoupper($metodo['card']->brand) }} - **** {{ substr($metodo['card']->last4, -4) }}
                                            ({{ $metodo['card']->exp_month }}/{{ $metodo['card']->exp_year }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @if(count($metodosPagamento) == 0)
                                <small style="font-size:12px;color:var(--muted);">Cadastre um metodo de pagamento para continuar.</small>
                            @endif
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary" {{ (($gatewayPadrao ?? 'stripe') === 'stripe' && count($metodosPagamento) == 0) ? 'disabled' : '' }}>
                        Efetuar pagamento
                    </button>
                </form>
            </div>
        </section>

        <aside class="card summary">
            <h3>Resumo</h3>
            <div class="sum"><span>Retirada</span><strong>{{ $reserva->estacaoRetirada?->nome ?? '-' }}</strong></div>
            <div class="sum"><span>Devolucao</span><strong>{{ $reserva->estacaoDevolucao?->nome ?? '-' }}</strong></div>
            <div class="sum"><span>Periodo</span><strong>{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }} ate {{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</strong></div>
            <div class="sum"><span>Grupo</span><strong>{{ $reserva->grupoFrota?->nome ?? '-' }}</strong></div>
            <div class="sum"><span>Modelo</span><strong>{{ $reserva->modelos?->nome ?? '-' }}</strong></div>
            <div class="sum total"><span>Total</span><strong>{{ $simbolo }} {{ number_format((float) ($reserva->valor_total_final ?? 0), 2, ',', '.') }}</strong></div>
        </aside>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva confirmada | {{ $empresa->nome }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #dbe4f0;
            --primary: #111827;
            --primary-2: #1e293b;
            --accent: #22c55e;
            --accent-soft: #e8f8ef;
            --shadow: 0 24px 60px rgba(15, 23, 42, .08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.9), transparent 42%),
                radial-gradient(circle at 85% 15%, rgba(34,197,94,.10), transparent 24%),
                linear-gradient(180deg, #fafcff 0%, var(--bg) 100%);
            color: var(--text);
            padding: 24px 16px 40px;
        }

        .wrap {
            width: min(980px, 100%);
            margin: 0 auto;
        }

        .shell {
            background: rgba(255,255,255,.72);
            border: 1px solid rgba(219,228,240,.95);
            border-radius: 24px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .hero {
            padding: 24px;
            background:
                linear-gradient(135deg, rgba(17,24,39,.96), rgba(31,41,55,.92) 45%, rgba(34,197,94,.92));
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }

        .logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 14px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.14);
            padding: 5px;
            flex: 0 0 auto;
        }

        .brand strong {
            font-size: 1.08rem;
            letter-spacing: .01em;
        }

        .brand small {
            display: block;
            color: rgba(255,255,255,.76);
            margin-top: 2px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 72px 1fr;
            gap: 18px;
            align-items: center;
        }

        .hero-icon {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            background: rgba(255,255,255,.16);
            display: grid;
            place-items: center;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.12);
        }

        .hero-icon i {
            font-size: 1.8rem;
            color: #fff;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            line-height: 1.05;
        }

        .hero p {
            margin: 0;
            color: rgba(255,255,255,.84);
            font-size: 1.02rem;
            line-height: 1.55;
        }

        .content {
            padding: 22px 24px 26px;
        }

        .actions-top {
            display: grid;
            grid-template-columns: 1.3fr .8fr .8fr;
            gap: 14px;
            margin-bottom: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 58px;
            border-radius: 999px;
            padding: 14px 20px;
            text-decoration: none;
            font-weight: 800;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-primary {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            box-shadow: 0 16px 28px rgba(15,23,42,.18);
        }

        .btn-secondary {
            background: #fff;
            border: 1px solid var(--line);
            color: var(--text);
            box-shadow: 0 8px 18px rgba(15,23,42,.04);
        }

        .btn i {
            font-size: .95rem;
            flex: 0 0 auto;
        }

        .quick-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--line);
        }

        .info-card {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: #fff;
            padding: 18px;
        }

        .info-card h2 {
            margin: 0 0 14px;
            font-size: 1.15rem;
        }

        .summary-grid {
            display: grid;
            gap: 0;
        }

        .summary-row {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 16px;
            align-items: start;
            padding: 14px 0;
            border-bottom: 1px dashed #dce5f0;
        }

        .summary-row:last-child { border-bottom: 0; }

        .summary-k {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            color: #1f2937;
        }

        .summary-k .mini {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: #f1f5f9;
            color: #475569;
            flex: 0 0 auto;
        }

        .summary-v {
            color: #334155;
            line-height: 1.4;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .badge-success {
            background: var(--accent-soft);
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-neutral {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .survey-wrap {
            margin-top: 18px;
            border: 1px solid #d9eadf;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 16px 36px rgba(15,23,42,.05);
        }

        .survey-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            background: linear-gradient(135deg, #f3fbf5, #eefaf1);
            border-bottom: 1px solid #d9eadf;
        }

        .survey-head-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .survey-head-icon {
            width: 54px;
            height: 54px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
        }

        .survey-head-icon i {
            font-size: 1.15rem;
            color: currentColor;
        }

        .survey-head h3 {
            margin: 0 0 4px;
            font-size: 1.08rem;
        }

        .survey-head p {
            margin: 0;
            color: var(--muted);
        }

        .survey-head .btn {
            min-height: 48px;
            padding-inline: 18px;
        }

        .survey-body {
            padding: 18px 20px 20px;
        }

        .survey-submitted {
            padding: 16px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #dbe4f0;
            color: #334155;
        }

        .survey-card {
            display: grid;
            gap: 18px;
        }

        .survey-block {
            padding: 14px 0 4px;
        }

        .survey-label {
            display: block;
            margin: 0 0 10px;
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
        }

        .survey-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .survey-option {
            position: relative;
            display: inline-flex;
        }

        .survey-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .survey-option span {
            min-width: 52px;
            height: 44px;
            padding: 0 14px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1rem;
            background: #fff;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
        }

        .survey-option input:checked + span {
            transform: translateY(-1px) scale(1.03);
            box-shadow: 0 10px 18px rgba(15,23,42,.08);
        }

        .survey-tone--1 { background:#b91c1c !important; color:#fff !important; border-color:#991b1b !important; }
        .survey-tone--2 { background:#dc2626 !important; color:#fff !important; border-color:#b91c1c !important; }
        .survey-tone--3 { background:#fde047 !important; color:#111827 !important; border-color:#facc15 !important; }
        .survey-tone--4 { background:#86efac !important; color:#064e3b !important; border-color:#4ade80 !important; }
        .survey-tone--5 { background:#166534 !important; color:#fff !important; border-color:#14532d !important; }

        .nps-tone--0 { background:#b91c1c !important; color:#fff !important; border-color:#991b1b !important; }
        .nps-tone--1 { background:#dc2626 !important; color:#fff !important; border-color:#b91c1c !important; }
        .nps-tone--2 { background:#ef4444 !important; color:#fff !important; border-color:#dc2626 !important; }
        .nps-tone--3 { background:#f97316 !important; color:#fff !important; border-color:#ea580c !important; }
        .nps-tone--4 { background:#fb923c !important; color:#fff !important; border-color:#f97316 !important; }
        .nps-tone--5 { background:#f59e0b !important; color:#fff !important; border-color:#d97706 !important; }
        .nps-tone--6 { background:#eab308 !important; color:#fff !important; border-color:#ca8a04 !important; }
        .nps-tone--7 { background:#facc15 !important; color:#111827 !important; border-color:#eab308 !important; }
        .nps-tone--8 { background:#4ade80 !important; color:#064e3b !important; border-color:#22c55e !important; }
        .nps-tone--9 { background:#16a34a !important; color:#fff !important; border-color:#15803d !important; }
        .nps-tone--10 { background:#166534 !important; color:#fff !important; border-color:#14532d !important; }

        .survey-note {
            margin-top: 8px;
            font-size: .82rem;
            color: #64748b;
        }

        .textarea {
            width: 100%;
            min-height: 120px;
            resize: vertical;
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            padding: 12px 14px;
            outline: none;
            font: inherit;
        }

        .textarea::placeholder { color: #94a3b8; }

        .section-title {
            margin: 0 0 14px;
            font-size: 1.08rem;
        }

        .muted {
            color: var(--muted);
        }

        @media (max-width: 860px) {
            .actions-top,
            .quick-row {
                grid-template-columns: 1fr;
            }

            .summary-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .survey-head {
                flex-direction: column;
                align-items: stretch;
            }

            .survey-head-left {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    @php
        $satisfacao = $reserva->satisfacao;
        $surveyToneMap15 = [
            1 => 'survey-tone--1',
            2 => 'survey-tone--2',
            3 => 'survey-tone--3',
            4 => 'survey-tone--4',
            5 => 'survey-tone--5',
        ];
        $surveyToneMap010 = [
            0 => 'nps-tone--0',
            1 => 'nps-tone--1',
            2 => 'nps-tone--2',
            3 => 'nps-tone--3',
            4 => 'nps-tone--4',
            5 => 'nps-tone--5',
            6 => 'nps-tone--6',
            7 => 'nps-tone--7',
            8 => 'nps-tone--8',
            9 => 'nps-tone--9',
            10 => 'nps-tone--10',
        ];
        $dataInicio = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio) : null;
        $dataFim = $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim) : null;
        $retiradaTexto = trim(($reserva->estacaoRetirada?->nome ?? '-') . ' ' . ($dataInicio ? $dataInicio->format('d/m/Y H:i') : '-'));
        $devolucaoTexto = trim(($reserva->estacaoDevolucao?->nome ?? '-') . ' ' . ($dataFim ? $dataFim->format('d/m/Y H:i') : '-'));
        $totalReserva = $reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? $reserva->valor_total_tarifa_base ?? $reserva->tarifa_base * max(1, (int) ($reserva->quantidade_dias ?? 1)) ?? 0;
        $pagamentoPendente = !$reserva->isPagamentoEfetuado();
    @endphp

    <main class="wrap">
        <section class="shell">
            <div class="hero">
                <div class="brand">
                    @if(!empty($empresa->logo_url))
                        <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="logo">
                    @endif
                    <div>
                        <strong>{{ $empresa->nome }}</strong>
                    </div>
                </div>

                <div class="hero-grid">
                    <div class="hero-icon" aria-hidden="true">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div>
                        <h1>Reserva recebida com sucesso!</h1>
                        <p>Sua reserva foi registrada. Para confirmar sua locação, siga para o pagamento e finalize os próximos passos com segurança.</p>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="actions-top">
                    @if($pagamentoPendente && !empty($urlPagamento))
                        @if($isAuth)
                            <a href="{{ $urlPagamento }}" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i>
                                Ir para pagamento
                            </a>
                        @else
                            <a href="{{ $loginUrl }}" class="btn btn-primary">
                                <i class="fas fa-right-to-bracket"></i>
                                Fazer login para pagar
                            </a>
                        @endif
                    @endif
                    <a href="{{ $urlNovaReserva }}" class="btn btn-secondary">
                        <i class="fas fa-rotate-left"></i>
                        Nova reserva
                    </a>
                    <a href="{{ $urlAdmin }}" class="btn btn-secondary">
                        <i class="fas fa-globe"></i>
                        Voltar ao site
                    </a>
                </div>

                <div class="quick-row">
                    <div class="info-card">
                        <h2>Resumo da reserva</h2>
                        <div class="summary-grid">
                            <div class="summary-row">
                            <div class="summary-k"><span class="mini"><i class="fas fa-file-lines"></i></span> Protocolo</div>
                                <div class="summary-v">#{{ $reserva->id }}</div>
                            </div>
                            <div class="summary-row">
                            <div class="summary-k"><span class="mini"><i class="fas fa-user"></i></span> Cliente</div>
                                <div class="summary-v">{{ $reserva->usuario?->nome ?? 'Nao informado' }}</div>
                            </div>
                            <div class="summary-row">
                            <div class="summary-k"><span class="mini"><i class="fas fa-location-dot"></i></span> Retirada</div>
                                <div class="summary-v">{{ $retiradaTexto }}</div>
                            </div>
                            <div class="summary-row">
                            <div class="summary-k"><span class="mini"><i class="fas fa-rotate-left"></i></span> Devolução</div>
                                <div class="summary-v">{{ $devolucaoTexto }}</div>
                            </div>
                            <div class="summary-row">
                            <div class="summary-k"><span class="mini"><i class="fas fa-car-side"></i></span> Modelo</div>
                                <div class="summary-v">
                                    {{ $reserva->modelos?->nome ?? '-' }}
                                    @if(!empty($reserva->grupoFrota?->nome))
                                        <div class="muted">{{ $reserva->grupoFrota->nome }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="summary-row">
                            <div class="summary-k"><span class="mini"><i class="fas fa-money-bill-wave"></i></span> Total</div>
                                <div class="summary-v"><strong>{{ $empresa->simbolo_moeda ?? 'R$' }} {{ number_format((float) $totalReserva, 2, ',', '.') }}</strong></div>
                            </div>
                            <div class="summary-row">
                            <div class="summary-k"><span class="mini"><i class="fas fa-clock"></i></span> Pagamento</div>
                                <div class="summary-v">
                                    <span class="badge {{ $pagamentoPendente ? 'badge-neutral' : 'badge-success' }}">
                                        {{ $pagamentoPendente ? 'Pendente' : 'Pago' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h2>Próximos passos</h2>
                        <p class="muted" style="margin-top:0;line-height:1.6">
                            Revise os dados acima. Se o pagamento ainda não foi concluído, use o botão principal para finalizar.
                            Depois disso, sua reserva segue para confirmação operacional.
                        </p>
                        <div style="display:grid;gap:10px;margin-top:18px">
                            <div class="badge badge-success" style="justify-content:flex-start;">Reserva registrada</div>
                            <div class="badge badge-neutral" style="justify-content:flex-start;">Pagamento e validação</div>
                        </div>
                    </div>
                </div>

                <section class="survey-wrap">
                    <div class="survey-head">
                        <div class="survey-head-left">
                            <div class="survey-head-icon" aria-hidden="true">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <div>
                                <h3>Ajude a gente a melhorar</h3>
                                <p>Leva menos de 30 segundos.</p>
                            </div>
                        </div>
                        @if(!$satisfacao)
                            <a href="#avaliacao" class="btn btn-secondary">Responder avaliação</a>
                        @endif
                    </div>

                    <div class="survey-body" id="avaliacao">
                        @if($satisfacao)
                            <div class="survey-submitted">
                                <strong>Feedback já enviado.</strong>
                                <div style="margin-top:6px;">Obrigado pela sua avaliação. Sua resposta já foi salva.</div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('site.reserva.satisfacao.store', $reserva->id) }}" class="survey-card">
                                @csrf

                                <div class="survey-block">
                                    <label class="survey-label">1. Como foi sua experiência geral?</label>
                                    <div class="survey-options">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="survey-option">
                                                <input type="radio" name="satisfacao_geral" value="{{ $i }}" {{ old('satisfacao_geral') == $i ? 'checked' : '' }} required>
                                                <span class="{{ $surveyToneMap15[$i] ?? '' }}">{{ $i }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="survey-block">
                                    <label class="survey-label">2. Foi fácil concluir a reserva?</label>
                                    <div class="survey-options">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="survey-option">
                                                <input type="radio" name="facilidade_reserva" value="{{ $i }}" {{ old('facilidade_reserva') == $i ? 'checked' : '' }} required>
                                                <span class="{{ $surveyToneMap15[$i] ?? '' }}">{{ $i }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="survey-block">
                                    <label class="survey-label">3. De 0 a 10, quanto você recomendaria a empresa?</label>
                                    <div class="survey-options">
                                        @for($i = 0; $i <= 10; $i++)
                                            <label class="survey-option">
                                                <input type="radio" name="nps_nota" value="{{ $i }}" {{ old('nps_nota') !== null && (string) old('nps_nota') === (string) $i ? 'checked' : '' }} required>
                                                <span class="{{ $surveyToneMap010[$i] ?? '' }}">{{ $i }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                    <div class="survey-note">0 = não recomendaria | 10 = recomendaria com certeza</div>
                                </div>

                                <div class="survey-block">
                                    <label class="survey-label" for="comentario">O que podemos melhorar? <span style="font-weight:400;">(opcional)</span></label>
                                    <textarea id="comentario" name="comentario" class="textarea" placeholder="Uma sugestão rápida já ajuda bastante.">{{ old('comentario') }}</textarea>
                                </div>

                                <div style="display:flex;justify-content:flex-start;gap:10px;">
                                    <button type="submit" class="btn btn-primary">Enviar feedback</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </section>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());

            function showToast(message, icon) {
                if (!message || typeof Swal === 'undefined' || typeof Swal.fire !== 'function') {
                    return;
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: message,
                    showConfirmButton: false,
                    timer: 4500,
                    timerProgressBar: true,
                    width: 360,
                    background: icon === 'success' ? '#f0fdf4' : '#fef2f2',
                    color: icon === 'success' ? '#166534' : '#991b1b',
                });
            }

            if (successMessage) {
                showToast(successMessage, 'success');
            }

            if (Array.isArray(errorMessages)) {
                errorMessages.forEach(function (msg) {
                    showToast(msg, 'error');
                });
            }
        })();
    </script>
</body>
</html>

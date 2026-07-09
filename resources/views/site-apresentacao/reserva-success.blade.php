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
            background: #ffffff;
            color: #0f172a;
            display: grid;
            place-items: center;
            padding: 18px;
        }

        .card {
            width: min(720px, 100%);
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
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
            background: #f8fafc;
            padding: 4px;
            border: 1px solid #e2e8f0;
        }

        h1 { margin: 0 0 8px; font-size: 1.65rem; }
        p { margin: 0 0 14px; color: #475569; }

        .details {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px;
            margin-bottom: 16px;
        }

        .row {
            padding: 6px 0;
            font-size: .92rem;
            border-bottom: 1px dashed #e2e8f0;
        }

        .row:last-child { border-bottom: 0; }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .survey {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
        }

        .survey h2 {
            margin: 0 0 6px;
            font-size: 1.05rem;
        }

        .survey p.small {
            font-size: .88rem;
            color: #64748b;
        }

        .survey-card {
            margin-top: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
        }

        .survey-block {
            margin-bottom: 14px;
        }

        .survey-label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: .92rem;
        }

        .survey-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
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
            min-width: 42px;
            height: 40px;
            padding: 0 10px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            background: #fff;
            color: #0f172a;
            transition: all .15s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .survey-option input:checked + span {
            color: #0f172a;
            border-color: rgba(15, 23, 42, .2);
            box-shadow: 0 10px 18px rgba(15, 23, 42, .08);
        }

        .survey-tone--0 { background: #B91C1C !important; color: #fff !important; border-color: #991B1B !important; }
        .survey-tone--1 { background: #B91C1C !important; color: #fff !important; border-color: #991B1B !important; }
        .survey-tone--2 { background: #DC2626 !important; color: #fff !important; border-color: #B91C1C !important; }
        .survey-tone--3 { background: #FACC15 !important; color: #111827 !important; border-color: #EAB308 !important; }
        .survey-tone--4 { background: #4ADE80 !important; color: #064E3B !important; border-color: #22C55E !important; }
        .survey-tone--5 { background: #166534 !important; color: #fff !important; border-color: #14532D !important; }
        .survey-tone--6 { background: #EAB308 !important; color: #fff !important; border-color: #CA8A04 !important; }
        .survey-tone--7 { background: #FACC15 !important; color: #111827 !important; border-color: #EAB308 !important; }
        .survey-tone--8 { background: #4ADE80 !important; color: #064E3B !important; border-color: #22C55E !important; }
        .survey-tone--9 { background: #16A34A !important; color: #fff !important; border-color: #15803D !important; }
        .survey-tone--10 { background: #166534 !important; color: #fff !important; border-color: #14532D !important; }

        .nps-tone--0 { background: #B91C1C !important; color: #fff !important; border-color: #991B1B !important; }
        .nps-tone--1 { background: #DC2626 !important; color: #fff !important; border-color: #B91C1C !important; }
        .nps-tone--2 { background: #EF4444 !important; color: #fff !important; border-color: #DC2626 !important; }
        .nps-tone--3 { background: #F97316 !important; color: #fff !important; border-color: #EA580C !important; }
        .nps-tone--4 { background: #FB923C !important; color: #fff !important; border-color: #F97316 !important; }
        .nps-tone--5 { background: #F59E0B !important; color: #fff !important; border-color: #D97706 !important; }
        .nps-tone--6 { background: #EAB308 !important; color: #fff !important; border-color: #CA8A04 !important; }
        .nps-tone--7 { background: #FACC15 !important; color: #111827 !important; border-color: #EAB308 !important; }
        .nps-tone--8 { background: #4ADE80 !important; color: #064E3B !important; border-color: #22C55E !important; }
        .nps-tone--9 { background: #16A34A !important; color: #fff !important; border-color: #15803D !important; }
        .nps-tone--10 { background: #166534 !important; color: #fff !important; border-color: #14532D !important; }

        .survey-option input:checked + span.survey-tone--1,
        .survey-option input:checked + span.survey-tone--2,
        .survey-option input:checked + span.survey-tone--3,
        .survey-option input:checked + span.survey-tone--4,
        .survey-option input:checked + span.survey-tone--5,
        .survey-option input:checked + span.survey-tone--0,
        .survey-option input:checked + span.survey-tone--6,
        .survey-option input:checked + span.survey-tone--7,
        .survey-option input:checked + span.survey-tone--8,
        .survey-option input:checked + span.survey-tone--9,
        .survey-option input:checked + span.survey-tone--10 {
            transform: translateY(-1px) scale(1.03);
        }

        .survey-textarea {
            width: 100%;
            min-height: 84px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            padding: 10px 12px;
            resize: vertical;
            outline: none;
        }

        .survey-textarea::placeholder { color: #94a3b8; }

        .survey-note {
            font-size: .78rem;
            color: #64748b;
            margin-top: 6px;
        }

        .survey-submitted {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .btn {
            border-radius: 999px;
            padding: 10px 14px;
            font-weight: 700;
            text-decoration: none;
            font-size: .86rem;
        }

        .btn-primary {
            background: #0f172a;
            color: #ffffff;
        }

        .btn-secondary {
            border: 1px solid #cbd5e1;
            color: #0f172a;
            background: #fff;
        }
        .toast-wrap { position: fixed; top: 16px; right: 16px; display: grid; gap: 8px; z-index: 60; }
        .toast { min-width: 260px; max-width: 360px; border-radius: 12px; padding: 12px 14px; font-size: 13px; font-weight: 700; box-shadow: 0 18px 30px rgba(15, 23, 42, .12); border: 1px solid #e2e8f0; background: #fff; color: #0f172a; }
        .toast.ok { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .toast.err { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
    </style>
</head>
<body>
    <div class="toast-wrap" id="toastWrap"></div>
    <section class="card">
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
        @endphp
        <div class="header">
            @if(!empty($empresa->logo_url))
                <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="logo">
            @endif
            <strong>{{ $empresa->nome }}</strong>
        </div>

        <h1>Reserva recebida com sucesso</h1>
        <p>Seu atendimento foi registrado. Para seguir com o pagamento, clique no botao abaixo.</p>

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

        <div class="survey">
            <h2>Ajude a gente a melhorar</h2>
            <p class="small">Leva menos de 30 segundos. São perguntas rápidas para entendermos sua experiência.</p>

            @if($satisfacao)
                <div class="survey-submitted">
                    <strong>Feedback registrado.</strong>
                    <div class="mt-1">Obrigado pela contribuição! Sua resposta já foi salva.</div>
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

                    <div class="survey-block mb-0">
                        <label class="survey-label" for="comentario">O que podemos melhorar? <span style="font-weight:400;">(opcional)</span></label>
                        <textarea id="comentario" name="comentario" class="survey-textarea" placeholder="Uma sugestão rápida já ajuda bastante.">{{ old('comentario') }}</textarea>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Enviar feedback</button>
                    </div>
                </form>
            @endif
        </div>
    </section>
    <script>
        (function () {
            const toastWrap = document.getElementById('toastWrap');
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());

            const showToast = (message, type) => {
                if (!toastWrap || !message) return;
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastWrap.appendChild(toast);
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            };

            if (successMessage) {
                showToast(successMessage, 'ok');
            }
            if (Array.isArray(errorMessages)) {
                errorMessages.forEach((msg) => showToast(msg, 'err'));
            }
        })();
    </script>
</body>
</html>

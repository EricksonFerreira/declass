@extends('adminlte::page')

@section('title', 'Comandos do Sistema')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="cmd-hero mb-4">
    <div class="d-flex align-items-center" style="gap:14px;">
        <div class="cmd-hero__icon">
            <i class="fas fa-terminal"></i>
        </div>
        <div>
            <h4 class="mb-0 font-weight-bold text-white">Comandos do Sistema</h4>
            <small class="text-white-50">Execute comandos com um clique. Acesso restrito ao superadmin.</small>
        </div>
    </div>
</div>

{{-- Resultado da última execução --}}
<div id="cmdResultado" class="card mb-4" style="display:none;">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title text-sm font-weight-bold mb-0">
            <i class="fas fa-scroll mr-1"></i> <span id="cmdResultadoTitulo">Resultado</span>
        </h3>
        <span id="cmdResultadoDuracao" class="badge badge-secondary"></span>
    </div>
    <div class="card-body p-0">
        <pre id="cmdResultadoSaida" class="cmd-output mb-0"></pre>
    </div>
</div>

@foreach ($grupos as $grupo)
    <div class="cmd-grupo-titulo">
        <i class="{{ $grupo['icone'] }} mr-1"></i> {{ $grupo['titulo'] }}
    </div>

    <div class="cmd-grid mb-4">
        @foreach ($grupo['itens'] as $item)
            <div class="cmd-card" style="border-left-color: {{ $item['cor'] }};">
                <div class="cmd-card__icon" style="background: {{ $item['cor'] }}1a; color: {{ $item['cor'] }};">
                    <i class="{{ $item['icone'] }}"></i>
                </div>
                <div class="cmd-card__info">
                    <div class="cmd-card__nome">{{ $item['nome'] }}</div>
                    <div class="cmd-card__assinatura">
                        php artisan {{ $item['artisan'] }}@foreach(($item['params'] ?? []) as $k => $v){{ $v === true ? ' '.$k : ' '.$k.'='.$v }}@endforeach
                    </div>
                    @if (!empty($item['descricao']))
                        <div class="cmd-card__desc">{{ $item['descricao'] }}</div>
                    @endif
                </div>
                <button type="button"
                        class="cmd-card__play"
                        style="background: {{ $item['cor'] }};"
                        data-comando="{{ $item['key'] }}"
                        data-nome="{{ $item['nome'] }}"
                        @if (!empty($item['confirm'])) data-confirm="{{ $item['confirm'] }}" @endif
                        title="Executar {{ $item['nome'] }}">
                    <i class="fas fa-play"></i>
                </button>
            </div>
        @endforeach
    </div>
@endforeach
@endsection

@section('css')
<style>
    .cmd-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 14px;
        padding: 22px 26px;
    }

    .cmd-hero__icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: rgba(255,255,255,.1);
        color: #38bdf8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .cmd-grupo-titulo {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin: 18px 2px 10px;
    }

    .cmd-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }

    .cmd-card {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 14px;
        transition: box-shadow .15s ease, transform .15s ease;
    }

    .cmd-card:hover {
        box-shadow: 0 6px 18px rgba(15,23,42,.08);
        transform: translateY(-1px);
    }

    .cmd-card__icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
    }

    .cmd-card__info {
        flex: 1;
        min-width: 0;
    }

    .cmd-card__nome {
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cmd-card__assinatura {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: .68rem;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 2px;
    }

    .cmd-card__desc {
        font-size: .72rem;
        color: #94a3b8;
        margin-top: 4px;
    }

    .cmd-card__play {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 9px;
        color: #fff;
        font-size: .72rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        cursor: pointer;
        transition: filter .15s ease, transform .15s ease;
    }

    .cmd-card__play:hover { filter: brightness(1.12); transform: scale(1.06); }
    .cmd-card__play:disabled { opacity: .55; cursor: wait; transform: none; }

    .cmd-output {
        background: #0f172a;
        color: #a5f3fc;
        font-size: .78rem;
        line-height: 1.55;
        padding: 16px 18px;
        border-radius: 0 0 6px 6px;
        max-height: 380px;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .cmd-output--erro { color: #fda4af; }

    .cmd-card--executando { opacity: .7; pointer-events: none; }

    @media (max-width: 575.98px) {
        .cmd-hero { padding: 16px 18px; }
        .cmd-grid { grid-template-columns: 1fr; }
        .cmd-card__play { width: 40px; height: 40px; font-size: .8rem; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        const URL_EXECUTAR = "{{ route('comandos.executar') }}";
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
            || $('input[name="_token"]').first().val()
            || '{{ csrf_token() }}';

        function mostrarResultado(dados, ok) {
            const $card = $('#cmdResultado');
            $('#cmdResultadoTitulo').text((dados.artisan ? dados.artisan + ' — ' : '') + (dados.comando || 'Comando'));
            $('#cmdResultadoDuracao').text((dados.duracao ?? '-') + 's');
            $('#cmdResultadoSaida')
                .text(dados.saida || '(sem saída)')
                .toggleClass('cmd-output--erro', !ok);
            $card.show();
            $card[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        $(document).on('click', '.cmd-card__play', function () {
            const $btn = $(this);
            const confirmMsg = $btn.data('confirm');

            if (confirmMsg && !window.confirm(confirmMsg)) {
                return;
            }

            const $card = $btn.closest('.cmd-card');
            const iconeOriginal = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $card.addClass('cmd-card--executando');

            fetch(URL_EXECUTAR, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify({ comando: $btn.data('comando') }),
            })
                .then(async (resp) => {
                    const dados = await resp.json().catch(() => ({ saida: 'Resposta inválida do servidor.' }));
                    mostrarResultado(dados, resp.ok && dados.ok !== false);
                })
                .catch((erro) => {
                    mostrarResultado({ comando: $btn.data('nome'), saida: 'Falha na requisição: ' + erro.message }, false);
                })
                .finally(() => {
                    $btn.prop('disabled', false).html(iconeOriginal);
                    $card.removeClass('cmd-card--executando');
                });
        });
    });
</script>
@stop

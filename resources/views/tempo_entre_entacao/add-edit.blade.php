@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $origemSelecionada = old('estacao_origem_id', $tempo_entre_entacao->estacao_origem_id ?? null);
        $destinoSelecionado = old('estacao_destino_id', $tempo_entre_entacao->estacao_destino_id ?? null);
        $statusSelecionado = old('ativo', $tempo_entre_entacao->ativo ?? null);
        $tempoSelecionado = old('tempo_deslocamento', $tempo_entre_entacao->tempo_deslocamento ?? '');
        $opcoesDestino = isset($estacaoDestino) ? $estacaoDestino : [];
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-tempo-estacao shadow-sm border-0">
            <div class="card-header card-header-tempo-estacao border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure o tempo de deslocamento entre estacoes de origem e destino.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-tempo-estacao" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="tempo-estacao-section-title">Informacoes Basicas</div>
                    <div class="form-row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-tempo-estacao">
                                <x-form-select name="estacao_origem_id" label="Estacao de Origem" :options="$estacoesOrigem"
                                    placeholder="Selecione uma estacao" :selected="$origemSelecionada"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-tempo-estacao">
                                <x-form-select name="estacao_destino_id" label="Estacao de Destino" :options="$opcoesDestino"
                                    placeholder="Selecione uma estacao" :selected="$destinoSelecionado"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group form-group-tempo-estacao">
                                <x-form-input type="time" class="form-control form-control-sm" id="tempo_deslocamento"
                                    name="tempo_deslocamento" label="Tempo" value="{{ $tempoSelecionado }}" required />
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group form-group-tempo-estacao">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione"
                                    :selected="$statusSelecionado" class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-tempo-estacao col-12 mt-2">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-tempo-estacao {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-tempo-estacao {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-tempo-estacao h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .tempo-estacao-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-tempo-estacao {
        margin-bottom: 1rem;
    }

    .card-tempo-estacao .form-control,
    .card-tempo-estacao .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-tempo-estacao .form-control:focus,
    .card-tempo-estacao .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-tempo-estacao {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-tempo-estacao {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-tempo-estacao h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-tempo-estacao p {
            font-size: .85rem;
        }

        .tempo-estacao-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-tempo-estacao {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-tempo-estacao {
            margin-bottom: .8rem;
        }

        .card-tempo-estacao .form-control,
        .card-tempo-estacao .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script>
        $(function () {
            const $origem = $('select[name="estacao_origem_id"]');
            const $destino = $('select[name="estacao_destino_id"]');
            const destinoInicial = @json($destinoSelecionado);

            function carregarDestinos(estacaoOrigemId, destinoParaSelecionar = null) {
                if (!estacaoOrigemId) {
                    $destino.empty().append('<option value="">Selecione uma estacao</option>');
                    return;
                }

                $.ajax({
                    url: "{{ route('estacao.getEstacoesDisponiveis') }}",
                    type: 'GET',
                    data: { estacao_origem_id: estacaoOrigemId },
                    success: function (data) {
                        $destino.empty();
                        $destino.append('<option value="">Selecione uma estacao</option>');

                        $.each(data, function (_, value) {
                            $destino.append('<option value="' + value.id + '">' + value.nome + '</option>');
                        });

                        if (destinoParaSelecionar) {
                            $destino.val(String(destinoParaSelecionar));
                        }
                    }
                });
            }

            $origem.on('change', function () {
                carregarDestinos($(this).val(), null);
            });

            if ($origem.val() && ($destino.find('option').length <= 1 || destinoInicial)) {
                carregarDestinos($origem.val(), destinoInicial);
            }
        });
    </script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'tempo-entre-estacao-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.card-header-tempo-estacao', popover: { title: 'Cadastro de tempo entre estações', description: 'Nesta tela você define o tempo estimado de deslocamento entre uma estação de origem e uma estação de destino.', side: 'bottom' } },
        { element: '#formulario-tempo-estacao', popover: { title: 'Formulário do trajeto', description: 'Preencha origem, destino, tempo e status para cadastrar corretamente o deslocamento.', side: 'top' } },
        { element: 'select[name="estacao_origem_id"]', popover: { title: 'Estação de origem', description: 'Escolha a estação de partida. O sistema atualiza automaticamente as opções válidas de destino.', side: 'bottom' } },
        { element: 'select[name="estacao_destino_id"]', popover: { title: 'Estação de destino', description: 'Selecione para onde o veículo será deslocado, considerando os destinos disponíveis para a origem escolhida.', side: 'bottom' } },
        { element: '#tempo_deslocamento', popover: { title: 'Tempo previsto', description: 'Informe a duração estimada do trajeto para apoiar reservas, check-ins e movimentações internas.', side: 'bottom' } },
        { element: '.btn-save-tempo-estacao', popover: { title: 'Salvar configuração', description: 'Após revisar os dados, salve para disponibilizar o tempo entre estações no sistema.', side: 'top' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            allowClose: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: steps,
        });
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(() => buildTour().drive());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>
@stop

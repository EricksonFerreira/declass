@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
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
        <div class="card card-tipo-frota shadow-sm border-0">
            <div class="card-header card-header-tipo-frota border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados do tipo de frota e suas caracteristicas.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="tipo-frota-section-title">Informacoes Basicas</div>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <div class="form-group form-group-tipo-frota">
                            <x-form-input type="text" class="form-control form-control-sm" id="codigo" name="codigo"
                                label="Codigo" value="{{ old('codigo', $tipo_frota->codigo ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-5">
                        <div class="form-group form-group-tipo-frota">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ old('nome', $tipo_frota->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-3">
                        <div class="form-group form-group-tipo-frota">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                :selected="old('ativo', isset($tipo_frota->ativo) && $tipo_frota->ativo ? $tipo_frota->ativo : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-12 mt-1">
                        <div class="tipo-frota-section-title">Caracteristicas</div>
                        <div class="tipo-frota-caracteristicas-wrap">
                            @foreach ($caracteristicas as $c)
                                <label class="tipo-frota-check-chip">
                                    <input type="checkbox" name="caracteristicas[]" value="{{ $c->id }}"
                                        {{ isset($caracteristicasTipoFrota) && in_array($c->id, $caracteristicasTipoFrota) ? 'checked' : '' }}>
                                    <span>{{ $c->nome }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-tipo-frota col-12 mt-3">
                            Salvar
                        </button>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-tipo-frota {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-tipo-frota {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-tipo-frota h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .tipo-frota-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-tipo-frota {
        margin-bottom: 1rem;
    }

    .card-tipo-frota .form-control,
    .card-tipo-frota .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-tipo-frota .form-control:focus,
    .card-tipo-frota .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .tipo-frota-caracteristicas-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tipo-frota-check-chip {
        margin: 0;
        cursor: pointer;
    }

    .tipo-frota-check-chip input {
        display: none;
    }

    .tipo-frota-check-chip span {
        display: inline-block;
        border: 1px solid #d8e1ec;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        background: #fff;
        transition: all .15s ease;
    }

    .tipo-frota-check-chip input:checked + span {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .btn-save-tipo-frota {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-tipo-frota {
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

        .card-header-tipo-frota h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-tipo-frota p {
            font-size: .85rem;
        }

        .tipo-frota-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-tipo-frota {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-tipo-frota {
            margin-bottom: .8rem;
        }

        .card-tipo-frota .form-control,
        .card-tipo-frota .form-select {
            min-height: 36px;
            font-size: 13px;
        }

        .tipo-frota-check-chip span {
            font-size: 11px;
            padding: 5px 9px;
        }
    }
</style>
@stop

@section('js')
    <script></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'tipo-frota-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.card-header-tipo-frota', popover: { title: 'Cadastro de tipo de frota', description: 'Aqui você cria ou edita um tipo de frota. O tipo classifica grupos de veículos como Hatch, Sedan, SUV e pode ter características padrão vinculadas.', side: 'bottom' } },
        { element: '#codigo', popover: { title: 'Código', description: 'Informe um código curto e único para identificar o tipo, como HB para Hatchback ou SU para SUV. Usado em relatórios e integrações.', side: 'bottom' } },
        { element: '#nome', popover: { title: 'Nome do tipo', description: 'Nome completo do tipo de frota exibido nas listagens e configurações de grupo, como "Hatchback" ou "SUV de Grande Porte".', side: 'bottom' } },
        { element: 'select[name="ativo"]', popover: { title: 'Status', description: 'Defina se este tipo de frota estará ativo e disponível para ser vinculado a grupos de veículos.', side: 'bottom' } },
        { element: '.tipo-frota-caracteristicas-wrap', popover: { title: 'Características padrão', description: 'Marque as características que todos os grupos deste tipo normalmente possuem. Servem como sugestão ao criar novos grupos de frota.', side: 'top' } },
        { element: '.btn-save-tipo-frota', popover: { title: 'Salvar', description: 'Clique para gravar o tipo de frota. Você será redirecionado para a lista após a confirmação.', side: 'top' } },
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

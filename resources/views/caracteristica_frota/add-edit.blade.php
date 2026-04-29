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
        <div class="card card-caracteristica-frota shadow-sm border-0">
            <div class="card-header card-header-caracteristica-frota border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados da caracteristica de frota.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="caracteristica-frota-intro">
                    <div>
                        <h5>Cadastro visual de caracteristicas</h5>
                        <p>Defina o nome, tipo de valor e uma imagem para destacar no site.</p>
                    </div>
                    <div class="caracteristica-frota-intro__badge">Frota</div>
                </div>
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row" enctype="multipart/form-data">
                    <div class="col-12">
                        <div class="caracteristica-frota-section-title">Informacoes Basicas</div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="form-group form-group-caracteristica-frota">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ old('nome', $caracteristicaFrota->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="form-group form-group-caracteristica-frota">
                            <x-form-select name="tipo_valor_caracteristica_frota_id" label="Tipo do valor" :options="$valorCaracteristicaTipoFrota" placeholder="Selecione..."
                                :selected="old('tipo_valor_caracteristica_frota_id', isset($caracteristicaFrota->tipo_valor_caracteristica_frota_id) && $caracteristicaFrota->tipo_valor_caracteristica_frota_id ? $caracteristicaFrota->tipo_valor_caracteristica_frota_id : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <div class="form-group form-group-caracteristica-frota">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione..."
                                :selected="old('ativo', isset($caracteristicaFrota->ativo) && $caracteristicaFrota->ativo ? $caracteristicaFrota->ativo : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-8">
                        <div class="form-group form-group-caracteristica-frota">
                            <label for="imagem">Imagem</label>
                            <x-input-file id="imagem" name="imagem" placeholder="Selecione a imagem..." legend="Buscar" />
                            <small class="text-muted d-block mt-2">Use imagens quadradas para melhor visual.</small>
                        </div>
                    </div>

                    <div class="col-12">
                        @php
                            $imagemAtual = optional($caracteristicaFrota ?? null)->imagem_url;
                        @endphp
                        <div class="caracteristica-frota-image-preview">
                            @if(!empty($imagemAtual))
                                <img src="{{ $imagemAtual }}" alt="Imagem da caracteristica {{ $caracteristicaFrota->nome ?? '' }}">
                            @else
                                <span>Sem imagem cadastrada</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group form-group-caracteristica-frota">
                            <label for="descricao">Descricao</label>
                            <textarea class="form-control form-control-sm" name="descricao" id="descricao" rows="7">{{ old('descricao', $caracteristicaFrota->descricao ?? null) }}</textarea>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-caracteristica-frota col-12 mt-2">
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
    .card-caracteristica-frota {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-caracteristica-frota {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-caracteristica-frota h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .caracteristica-frota-intro {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        background: #f8fbff;
        border: 1px solid #e4ecf7;
        border-radius: 12px;
        margin-bottom: 16px;
    }

    .caracteristica-frota-intro h5 {
        margin: 0 0 4px;
        font-size: 1rem;
        font-weight: 700;
        color: #0f2740;
    }

    .caracteristica-frota-intro p {
        margin: 0;
        font-size: .85rem;
        color: #5b6b82;
    }

    .caracteristica-frota-intro__badge {
        background: #1e4fd8;
        color: #fff;
        font-weight: 700;
        font-size: .75rem;
        padding: 6px 12px;
        border-radius: 999px;
        box-shadow: 0 6px 14px rgba(30, 79, 216, .2);
        white-space: nowrap;
    }

    .caracteristica-frota-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-caracteristica-frota {
        margin-bottom: 1rem;
    }

    .card-caracteristica-frota .form-control,
    .card-caracteristica-frota .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-caracteristica-frota .form-control:focus,
    .card-caracteristica-frota .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-caracteristica-frota {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    .caracteristica-frota-image-preview {
        border: 1px dashed #d7e1ee;
        border-radius: 12px;
        padding: 12px;
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8fbff 0%, #f2f7ff 100%);
        margin-bottom: 1rem;
    }

    .caracteristica-frota-image-preview img {
        max-height: 160px;
        max-width: 100%;
        border-radius: 10px;
        object-fit: contain;
    }

    .caracteristica-frota-image-preview span {
        color: #7b8798;
        font-size: .85rem;
    }

    @media (max-width: 991.98px) {
        .card-header-caracteristica-frota {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .caracteristica-frota-intro {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-caracteristica-frota h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-caracteristica-frota p {
            font-size: .85rem;
        }

        .caracteristica-frota-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-caracteristica-frota {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-caracteristica-frota {
            margin-bottom: .8rem;
        }

        .card-caracteristica-frota .form-control,
        .card-caracteristica-frota .form-select {
            min-height: 36px;
            font-size: 13px;
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
    const TOUR_ID     = 'caracteristica-frota-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.card-header-caracteristica-frota', popover: { title: 'Cadastro de característica', description: 'Nesta tela você define uma característica de frota que poderá ser vinculada a tipos e grupos de veículos.', side: 'bottom' } },
        { element: '.caracteristica-frota-intro', popover: { title: 'Sobre o cadastro', description: 'Use nome objetivo, tipo de valor adequado e uma imagem quadrada para destacar a característica no site.', side: 'bottom' } },
        { element: '#nome', popover: { title: 'Nome da característica', description: 'Informe um nome claro que identifique o atributo, como "Ar-condicionado" ou "Câmbio automático".', side: 'bottom' } },
        { element: 'select[name="tipo_valor_caracteristica_frota_id"]', popover: { title: 'Tipo do valor', description: 'Selecione como o valor desta característica será representado: texto, número, booleano ou outro formato.', side: 'bottom' } },
        { element: 'select[name="ativo"]', popover: { title: 'Status', description: 'Defina se esta característica estará disponível para ser vinculada a novos tipos e grupos de frota.', side: 'bottom' } },
        { element: '#imagem', popover: { title: 'Imagem ilustrativa', description: 'Envie uma imagem para representar visualmente a característica. Prefira imagens quadradas para melhor exibição.', side: 'bottom' } },
        { element: '.caracteristica-frota-image-preview', popover: { title: 'Prévia da imagem', description: 'A imagem atual cadastrada é exibida aqui. Ao selecionar uma nova, ela substituirá a imagem anterior.', side: 'top' } },
        { element: '#descricao', popover: { title: 'Descrição', description: 'Descreva o que esta característica representa para orientar a equipe ao vinculá-la nos tipos de frota.', side: 'top' } },
        { element: '.btn-save-caracteristica-frota', popover: { title: 'Salvar', description: 'Clique para gravar a característica. Ela ficará disponível para seleção nos formulários de tipos e grupos de frota.', side: 'top' } },
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

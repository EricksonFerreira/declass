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
        <div class="card card-tipo-movimentacao shadow-sm border-0">
            <div class="card-header card-header-tipo-movimentacao border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados principais do tipo de movimentacao.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-tipo-movimentacao" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="tipo-movimentacao-section-title">Informacoes Basicas</div>
                    <div class="form-row">
                        <div class="col-lg-6">
                            <div class="form-group form-group-tipo-movimentacao">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $tipo_movimentacao->nome ?? '') }}" required />
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-5">
                            <div class="form-group form-group-tipo-movimentacao">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                    :selected="old('ativo', isset($tipo_movimentacao->ativo) && $tipo_movimentacao->ativo ? $tipo_movimentacao->ativo : null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <div class="tipo-movimentacao-section-title">Descricao</div>
                    <div class="form-row">
                        <div class="col-12">
                            <div class="form-group form-group-tipo-movimentacao mb-0">
                                <label for="descricao" class="form-label-tipo-movimentacao">Descricao</label>
                                <textarea name="descricao" id="descricao" rows="5" class="form-control form-control-sm">{{ old('descricao', isset($tipo_movimentacao->descricao) ? $tipo_movimentacao->descricao : '') }}</textarea>
                                <x-form-errors name="descricao" />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-tipo-movimentacao col-12 mt-3">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-tipo-movimentacao {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-tipo-movimentacao {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-tipo-movimentacao h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .tipo-movimentacao-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-tipo-movimentacao {
        margin-bottom: 1rem;
    }

    .form-label-tipo-movimentacao {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .card-tipo-movimentacao .form-control,
    .card-tipo-movimentacao .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-tipo-movimentacao .form-control:focus,
    .card-tipo-movimentacao .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-tipo-movimentacao {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-tipo-movimentacao {
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

        .card-header-tipo-movimentacao h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-tipo-movimentacao p {
            font-size: .85rem;
        }

        .tipo-movimentacao-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-tipo-movimentacao {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-tipo-movimentacao {
            margin-bottom: .8rem;
        }

        .card-tipo-movimentacao .form-control,
        .card-tipo-movimentacao .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'tipo-movimentacao-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.card-header-tipo-movimentacao', popover: { title: 'Cadastro do tipo', description: 'Nesta tela você define o tipo de movimentação que poderá ser escolhido ao registrar deslocamentos de veículos.', side: 'bottom' } },
        { element: '#formulario-tipo-movimentacao', popover: { title: 'Formulário principal', description: 'Preencha nome, status e descrição para deixar o tipo pronto para uso nas operações.', side: 'top' } },
        { element: '#nome', popover: { title: 'Nome do tipo', description: 'Use um nome objetivo para identificar a finalidade da movimentação no processo operacional.', side: 'bottom' } },
        { element: 'select[name="ativo"]', popover: { title: 'Disponibilidade', description: 'Defina se este tipo estará ativo para ser selecionado em novos registros de movimentação.', side: 'bottom' } },
        { element: '#descricao', popover: { title: 'Descrição do tipo', description: 'Descreva quando esse tipo deve ser usado para orientar a equipe no cadastro das movimentações.', side: 'top' } },
        { element: '.btn-save-tipo-movimentacao', popover: { title: 'Salvar', description: 'Clique aqui para gravar o tipo de movimentação após revisar os dados informados.', side: 'top' } },
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

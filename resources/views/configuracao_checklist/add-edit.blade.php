@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $oldItensChecklist = old('item_checklist_id');
        $itensSelecionados = is_array($oldItensChecklist) ? array_map('strval', $oldItensChecklist) : [];

        if (empty($itensSelecionados) && isset($configuracaochecklist) && isset($configuracaochecklist->itemChecklist)) {
            $itensSelecionados = $configuracaochecklist->itemChecklist->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }

        $usarPadraoLegacy = empty($itensSelecionados) && !is_array($oldItensChecklist) && !isset($configuracaochecklist);

        $qtdTiposChecklist = is_countable($tipoChecklist ?? null) ? count($tipoChecklist) : 0;
        $qtdItensChecklist = is_countable($itemChecklist ?? null) ? count($itemChecklist) : 0;

        $faltasPreRequisitosConfiguracaoChecklist = [];
        if ($qtdTiposChecklist === 0) {
            $faltasPreRequisitosConfiguracaoChecklist[] = 'Cadastre pelo menos um tipo de checklist para configurar o checklist.';
        }
        if ($qtdItensChecklist === 0) {
            $faltasPreRequisitosConfiguracaoChecklist[] = 'Cadastre pelo menos um item de checklist para montar a configuracao.';
        }

        $acoesPreRequisitosConfiguracaoChecklist = [];
        if ($qtdTiposChecklist === 0 && \Illuminate\Support\Facades\Route::has('tipo_checklist.create')) {
            $acoesPreRequisitosConfiguracaoChecklist[] = ['label' => 'Cadastrar tipo de checklist', 'url' => route('tipo_checklist.create')];
        }
        if ($qtdItensChecklist === 0 && \Illuminate\Support\Facades\Route::has('item_checklist.create')) {
            $acoesPreRequisitosConfiguracaoChecklist[] = ['label' => 'Cadastrar item de checklist', 'url' => route('item_checklist.create')];
        }

        $podeCriarConfiguracaoChecklist = empty($faltasPreRequisitosConfiguracaoChecklist);
        $bloquearSalvarConfiguracaoChecklist = strtoupper($metodo ?? 'POST') === 'POST' && !$podeCriarConfiguracaoChecklist;
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
        <x-pre-requisitos-alerta
            title="Pre-requisitos pendentes para criar configuracao de checklist"
            :chips="[
                ['label' => 'Tipos de Checklist', 'value' => $qtdTiposChecklist],
                ['label' => 'Itens de Checklist', 'value' => $qtdItensChecklist],
            ]"
            :missing-items="$faltasPreRequisitosConfiguracaoChecklist"
            :action-links="$acoesPreRequisitosConfiguracaoChecklist"
        />

        <div class="card card-config-checklist shadow-sm border-0">
            <div class="card-header card-header-config-checklist border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Defina o tipo e os itens que formam esta configuracao de checklist.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-config-checklist" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="config-checklist-section-title">Informacoes Basicas</div>
                    <div class="form-row">
                        <div class="col-lg-5 col-md-7">
                            <div class="form-group form-group-config-checklist">
                                <x-form-select
                                    :options="$tipoChecklist"
                                    name="tipo_checklist_id"
                                    label="Tipo de Checklist"
                                    placeholder="Selecione um valor"
                                    :selected="old('tipo_checklist_id', isset($configuracaochecklist->tipo_checklist_id) ? $configuracaochecklist->tipo_checklist_id : null)"
                                    class="form-control form-control-sm"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <div class="config-checklist-section-title">Itens do Checklist</div>
                    <div class="checklist-actions mb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="alterarTodosItens(true)">
                            <i class="fa fa-check mr-1"></i> Selecionar Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="alterarTodosItens(false)">
                            <i class="fa fa-times mr-1"></i> Remover Todos
                        </button>
                    </div>

                    <div class="checklist-items-grid">
                        @foreach ($itemChecklist as $key => $value)
                            @php
                                $itemId = (string) $key;
                                $itemMarcado = in_array($itemId, $itensSelecionados, true) || ($usarPadraoLegacy && $loop->iteration % 4 === 1);
                            @endphp

                            <label class="checklist-item-card" for="item_checklist_{{ $key }}">
                                <input
                                    type="checkbox"
                                    class="checklist-item-card__checkbox"
                                    id="item_checklist_{{ $key }}"
                                    name="item_checklist_id[]"
                                    value="{{ $key }}"
                                    {{ $itemMarcado ? 'checked' : '' }}
                                >
                                <span class="checklist-item-card__label">{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-config-checklist col-12 mt-3" {{ $bloquearSalvarConfiguracaoChecklist ? 'disabled' : '' }}>
                        Salvar
                    </button>
                    @if ($bloquearSalvarConfiguracaoChecklist)
                        <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                            Cadastre os itens obrigatorios acima para liberar a criacao da configuracao de checklist.
                        </small>
                    @endif
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-config-checklist {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-config-checklist {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-config-checklist h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .config-checklist-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-config-checklist {
        margin-bottom: 1rem;
    }

    .card-config-checklist .form-control,
    .card-config-checklist .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-config-checklist .form-control:focus,
    .card-config-checklist .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .checklist-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .checklist-items-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
    }

    .checklist-item-card {
        margin: 0;
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem .65rem;
        border: 1px solid #d8e1ec;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: all .15s ease;
    }

    .checklist-item-card:hover {
        border-color: #4b80ff;
        background: #f8fbff;
    }

    .checklist-item-card__checkbox {
        width: 16px;
        height: 16px;
        margin: 0;
        accent-color: #2563eb;
        flex-shrink: 0;
    }

    .checklist-item-card__label {
        font-size: 13px;
        color: #1f2937;
        line-height: 1.3;
    }

    .btn-save-config-checklist {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 1199.98px) {
        .checklist-items-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .card-header-config-checklist {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .checklist-items-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-config-checklist h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-config-checklist p {
            font-size: .85rem;
        }

        .config-checklist-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-config-checklist {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-config-checklist {
            margin-bottom: .8rem;
        }

        .card-config-checklist .form-control,
        .card-config-checklist .form-select {
            min-height: 36px;
            font-size: 13px;
        }

        .checklist-items-grid {
            grid-template-columns: 1fr;
        }

        .checklist-item-card__label {
            font-size: 12px;
        }
    }
</style>
@stop

@section('js')
    <script>
        function alterarTodosItens(checked = true) {
            $('[name="item_checklist_id[]"]').prop('checked', checked);
        }
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'configuracao-checklist-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.card-config-checklist', popover: { title: 'Configuração de Checklist', description: 'Aqui você define quais itens compõem um checklist. As configurações são usadas no check-in e checkout para garantir a inspeção padronizada de cada veículo.' } },
        { element: '#formulario-config-checklist [name="nome"]', popover: { title: 'Nome da configuração', description: 'Dê um nome descritivo à configuração, como "Checklist Padrão Retirada" ou "Inspeção Completa Devolução". Este nome aparece na seleção durante o check-in.', side: 'bottom' } },
        { element: '#formulario-config-checklist [name="tipo_checklist_id"]', popover: { title: 'Tipo de checklist', description: 'Selecione a categoria desta configuração (Retirada, Devolução, Manutenção). O tipo determina em qual momento do fluxo este checklist é aplicado.', side: 'bottom' } },
        { element: '.checklist-items-grid', popover: { title: 'Itens do checklist', description: 'Marque os itens que devem ser verificados nesta configuração (pneus, lataria, vidros, documentos). Itens marcados aparecerão na tela de inspeção.', side: 'top' } },
        { element: '.btn-save-config-checklist', popover: { title: 'Salvar configuração', description: 'Clique para gravar. A configuração ficará disponível para uso nos check-ins e checkouts das reservas.', side: 'top' } },
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

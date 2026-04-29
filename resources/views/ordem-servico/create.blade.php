@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="os-form-page">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="mb-2"><i class="icon fas fa-ban"></i> Atencao</h5>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-9 col-lg-8">
                <div class="card p-2 os-main-card">
                    <div class="card-body">
                        <form method="post" action="{{ route('ordem-servico.store') }}">
                            @csrf

                            <div class="os-section-title">Dados da ordem de servico</div>
                            <div class="row">
                                <div class="col-md-8">
                                <div class="form-group">
                                    <label for="veiculo_id">Veiculo</label>
                                        <div class="d-flex align-items-end">
                                            <select name="veiculo_id" id="veiculo_id" class="form-control form-control-sm @error('veiculo_id') is-invalid @enderror" required>
                                                <option value="">Selecione um veiculo</option>
                                                @foreach($veiculos as $v)
                                                    <option value="{{ $v->id }}" @selected((int) old('veiculo_id', $veiculoId ?? null) === (int) $v->id)>
                                                        {{ $v->nome }} ({{ $v->placa ?? $v->id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-secondary btn-sm ml-2" id="btnBuscarVeiculoOS" title="Busca avancada de veiculos">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1">Use a busca avancada para filtrar por placa, grupo, marca, status e mais.</small>
                                        @error('veiculo_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                </div>
                            </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="reserva_id">Reserva (opcional)</label>
                                        <input type="number" name="reserva_id" id="reserva_id"
                                            class="form-control form-control-sm @error('reserva_id') is-invalid @enderror"
                                            value="{{ old('reserva_id') }}" min="1" placeholder="ID da reserva">
                                        @error('reserva_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="os-section-title">Avarias vinculadas (opcional)</div>
                            <div class="form-group">
                                <div id="danos_container" class="os-box-list">
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-arrow-up mr-1"></i>Selecione um veiculo para listar as avarias.
                                    </p>
                                </div>
                            </div>

                            <div class="os-section-title">Observacoes</div>
                            <div class="form-group">
                                <label for="observacoes">Observacoes gerais</label>
                                <textarea name="observacoes" id="observacoes"
                                    class="form-control form-control-sm @error('observacoes') is-invalid @enderror"
                                    rows="4" placeholder="Descreva o contexto desta OS...">{{ old('observacoes') }}</textarea>
                                @error('observacoes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="os-section-title">Servicos adicionais</div>
                            <div class="form-group mb-0">
                                <label for="servicos_livres">Um servico por linha</label>
                                <textarea name="servicos_livres" id="servicos_livres"
                                    class="form-control form-control-sm @error('servicos_livres') is-invalid @enderror"
                                    rows="5" placeholder="Ex: Troca de oleo&#10;Revisao geral">{{ old('servicos_livres') }}</textarea>
                                @error('servicos_livres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end flex-wrap gap-2 mt-4 os-actions">
                                <a href="{{ route('ordem-servico.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>Abrir ordem de servico
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4">
                <div class="card os-side-card">
                    <div class="card-body">
                        <h5 class="text-bold mb-2">Como funciona</h5>
                        <p class="text-muted small mb-3">
                            A OS pode ser aberta manualmente ou com avarias pendentes do veiculo.
                        </p>
                        <div class="os-tip">
                            <strong>1.</strong> Escolha o veiculo.
                        </div>
                        <div class="os-tip">
                            <strong>2.</strong> (Opcional) Marque as avarias que farao parte da OS.
                        </div>
                        <div class="os-tip">
                            <strong>3.</strong> Adicione servicos extras se necessario.
                        </div>
                        <div class="os-tip mb-0">
                            <strong>4.</strong> Conclua a OS depois para registrar as correcoes.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

<x-veiculo.busca-avancada-modal
    prefix="os"
    title="Buscar veiculo"
    trigger-id="btnBuscarVeiculoOS"
    target-select-id="veiculo_id"
    :filtro-marcas="$filtroMarcas"
    :filtro-grupos="$filtroGrupos"
    :filtro-tipos="$filtroTipos"
    :filtro-status="$filtroStatus"
/>

@section('css')
<style>
    .os-main-card,
    .os-side-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .os-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 14px;
        margin-top: 8px;
    }

    .os-box-list {
        border: 1px solid #d8e1ec;
        border-radius: 10px;
        padding: 12px;
        background: #f8fafc;
        min-height: 72px;
    }

    .os-tip {
        padding: 10px 12px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        margin-bottom: 10px;
        font-size: 13px;
        color: #475569;
    }

    .os-actions {
        gap: 8px;
    }
</style>
@stop

@section('js')
<script>
(function () {
    const veiculoEl = document.getElementById('veiculo_id');
    const container = document.getElementById('danos_container');
    const preselected = @json(array_values(array_map('intval', $danoIds ?? [])));
    const danosListUrl = @json(route('ordem-servico.danos-pendentes'));

    function renderDanos(list) {
        container.innerHTML = '';
        if (!list.length) {
            container.innerHTML = '<p class="text-muted small mb-0">Nenhuma avaria pendente disponivel para OS. Voce pode abrir a OS mesmo assim.</p>';
            return;
        }
        list.forEach(function (d) {
            const id = 'dano_cb_' + d.id;
            const wrap = document.createElement('div');
            wrap.className = 'custom-control custom-checkbox mb-2';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'custom-control-input';
            input.name = 'veiculo_dano_ids[]';
            input.value = d.id;
            input.id = id;
            if (preselected.indexOf(d.id) !== -1) input.checked = true;

            const label = document.createElement('label');
            label.className = 'custom-control-label';
            label.setAttribute('for', id);
            label.textContent = d.label;

            wrap.appendChild(input);
            wrap.appendChild(label);
            container.appendChild(wrap);
        });
    }

    function loadDanos() {
        const vid = veiculoEl.value;
        if (!vid) {
            container.innerHTML = '<p class="text-muted small mb-0">Selecione um veiculo para listar as avarias.</p>';
            return;
        }
        container.innerHTML = '<p class="text-muted small mb-0">Carregando...</p>';
        const sep = danosListUrl.indexOf('?') >= 0 ? '&' : '?';
        const url = danosListUrl + sep + 'veiculo_id=' + encodeURIComponent(vid);

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(renderDanos)
            .catch(function () {
                container.innerHTML = '<p class="text-danger small mb-0">Erro ao carregar avarias.</p>';
            });
    }

    if (!veiculoEl || !container) {
        return;
    }

    veiculoEl.addEventListener('change', loadDanos);
    if (veiculoEl.value) {
        loadDanos();
    }
})();
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'ordem-servico-create';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.os-main-card', popover: { title: 'Abrir Ordem de Serviço', description: 'Preencha os dados desta OS: veículo, danos pendentes que serão tratados, observações gerais e serviços adicionais não atrelados a danos específicos.' } },
        { element: '#veiculo_id', popover: { title: 'Selecionar veículo', description: 'Escolha o veículo que será colocado em manutenção. Você pode usar o campo de seleção ou a lupa de busca avançada para filtrar por marca, grupo ou placa.', side: 'bottom' } },
        { element: '#btnBuscarVeiculoOS', popover: { title: 'Busca avançada de veículo', description: 'Abre um modal com filtros avançados (marca, grupo, tipo, status) para localizar o veículo com mais precisão, especialmente útil em frotas grandes.', side: 'right' } },
        { element: '#danos_container', popover: { title: 'Danos vinculados', description: 'Lista os danos pendentes registrados para o veículo selecionado. Marque os danos que serão tratados nesta OS.', side: 'top' } },
        { element: '#observacoes', popover: { title: 'Observações gerais', description: 'Adicione notas sobre o estado do veículo, orientações para a equipe de manutenção ou qualquer informação relevante para a execução do serviço.', side: 'top' } },
        { element: '#servicos_livres', popover: { title: 'Serviços adicionais', description: 'Informe serviços que não estão atrelados a um dano específico, como troca de óleo ou revisão preventiva. Digite um serviço por linha.', side: 'top' } },
        { element: '.os-side-card', popover: { title: 'Dicas de preenchimento', description: 'O painel lateral orienta os 4 passos do processo: selecionar veículo, vincular danos, adicionar observações e confirmar abertura.', side: 'left' } },
        { element: '.os-actions button[type="submit"]', popover: { title: 'Abrir Ordem de Serviço', description: 'Clique aqui para registrar a OS. O veículo ficará em manutenção e os danos selecionados passarão para o status "em andamento".', side: 'top' } },
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

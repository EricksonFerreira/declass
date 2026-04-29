@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('tempo_entre_entacao.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $tempo_entre_entacaos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('tempo_entre_entacao.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $tempo_entre_entacaos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Estação Origem</th>
                        <th>Estação Destino</th>
                        <th>Tempo de Deslocamento</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tempo_entre_entacaos as $tempo_entre_entacao)
                        <tr>
                            <td>{{ $tempo_entre_entacao->estacaoOrigem->nome }}</td>
                            <td>{{ $tempo_entre_entacao->estacaoDestino->nome }}</td>
                            <td>{{ $tempo_entre_entacao->tempo_deslocamento }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $tempo_entre_entacao->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $tempo_entre_entacao->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('tempo_entre_entacao.edit', $tempo_entre_entacao->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('tempo_entre_entacao.destroy', $tempo_entre_entacao->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'tempo-entre-estacao-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.page-title', popover: { title: 'Tempos entre estações', description: 'Aqui você cadastra e consulta os tempos estimados de deslocamento entre estações de origem e destino.', side: 'bottom' } },
        { element: '.btn-create-resource, a[href*="tempo_entre_entacao/create"]', popover: { title: 'Novo tempo de deslocamento', description: 'Use este botão para informar uma nova combinação entre estação de origem, destino e tempo previsto.', side: 'left' } },
        { element: '.card-header', popover: { title: 'Resumo da listagem', description: 'O topo mostra total de registros, paginação e o seletor de quantidade exibida por página.', side: 'bottom' } },
        { element: 'table thead tr', popover: { title: 'Tabela de trajetos', description: 'Cada linha exibe a estação de origem, a estação de destino, o tempo configurado e o status do cadastro.', side: 'bottom' } },
        { element: 'table tbody tr:first-child td:last-child', popover: { title: 'Editar ou excluir', description: 'As ações permitem ajustar um trajeto cadastrado ou remover um vínculo que não será mais utilizado.', side: 'left' } },
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

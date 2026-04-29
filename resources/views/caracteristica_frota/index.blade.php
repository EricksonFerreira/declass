@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('caracteristica_frota.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $caracteristicaFrotas->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('caracteristica_frota.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $caracteristicaFrotas->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Status</th>
                        <th>Descricao</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($caracteristicaFrotas as $caracteristica_frota)
                        <tr>
                            <td>
                                <div class="caracteristica-thumb-wrap">
                                    @if(!empty($caracteristica_frota->imagem_url))
                                        <img src="{{ $caracteristica_frota->imagem_url }}" alt="Imagem {{ $caracteristica_frota->nome }}" class="caracteristica-thumb-img">
                                    @else
                                        <div class="caracteristica-thumb-empty">
                                            <i class="fa fa-image"></i>
                                            <span>Sem imagem</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $caracteristica_frota->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $caracteristica_frota->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $caracteristica_frota->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($caracteristica_frota->descricao))
                                    <button class="btn btn-xs btn-light btn-desc-toggle" type="button"
                                        data-toggle="collapse" data-target="#desc-{{ $caracteristica_frota->id }}"
                                        aria-expanded="false" aria-controls="desc-{{ $caracteristica_frota->id }}">
                                        Ver descricao
                                    </button>
                                @else
                                    <span class="text-muted">Sem descricao</span>
                                @endif
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('caracteristica_frota.edit', $caracteristica_frota->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('caracteristica_frota.destroy', $caracteristica_frota->id)" />
                            </td>
                        </tr>
                        <tr class="collapse-row">
                            <td colspan="5" class="p-0 border-0">
                                <div class="collapse" id="desc-{{ $caracteristica_frota->id }}">
                                    <div class="desc-content">
                                        {{ $caracteristica_frota->descricao }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
<style>
    .table.table-striped tbody tr td {
        vertical-align: middle;
    }

    .btn-desc-toggle {
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #d5dde8;
        background: #f8fafc;
    }

    .desc-content {
        background: #f8fbff;
        border: 1px dashed #d7e1ee;
        border-radius: 10px;
        padding: 12px 14px;
        margin: 6px 0 10px;
        color: #475569;
        font-size: .9rem;
    }

    .caracteristica-thumb-wrap {
        width: 96px;
        height: 72px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #f8fbff 0%, #eef3fb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        box-shadow: 0 10px 18px rgba(15, 23, 42, .08);
    }

    .caracteristica-thumb-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 10px;
        background: #fff;
        padding: 6px;
    }

    .caracteristica-thumb-empty {
        width: 100%;
        height: 100%;
        border-radius: 10px;
        border: 1px dashed #d5dde8;
        color: #94a3b8;
        font-size: .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 4px;
        background: #f8fafc;
    }

    .caracteristica-thumb-empty i {
        font-size: 1.1rem;
    }

    .table.table-striped tbody tr:hover td {
        background: #f4f8ff;
    }

    .card-header .card-title {
        color: #0f2740;
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
    const TOUR_ID     = 'caracteristica-frota-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.page-title', popover: { title: 'Lista de características de frota', description: 'Aqui você cadastra as características que podem ser vinculadas aos tipos e grupos de frota, como ar-condicionado, câmbio automático e outros atributos.' } },
        { element: '.btn-create-resource, a[href*="caracteristica_frota/create"]', popover: { title: 'Nova característica', description: 'Clique para cadastrar uma nova característica com nome, tipo de valor, imagem e descrição.', side: 'left' } },
        { element: '.card-header', popover: { title: 'Controle da listagem', description: 'O cabeçalho mostra o total de registros, paginação e o seletor de quantidade de itens exibidos por página.', side: 'bottom' } },
        { element: 'table thead tr', popover: { title: 'Tabela de características', description: 'A lista apresenta a imagem ilustrativa, nome da característica, status ativo ou inativo e a descrição resumida do atributo.', side: 'bottom' } },
        { element: 'table tbody tr:first-child td:last-child', popover: { title: 'Ações por registro', description: 'Em cada linha você pode editar os dados da característica ou excluir o cadastro quando não for mais utilizado.', side: 'left' } },
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

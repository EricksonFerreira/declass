@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('dano.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $danos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('dano.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $danos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Localização</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($danos as $dano)
                        <tr>
                            <td>{{ $dano->nome }}</td>
                            <td>{{ $dano->categoria?->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{$dano->localizacao == 'interior' ? 'warning' : 'dark'}}">
                                    {{ $dano->localizacao }}
                                </span>
                            </td>
                            <td>{{ $dano->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $dano->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $dano->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('dano.edit', $dano->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('dano.destroy', $dano->id)" />
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
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'dano-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
{ element: '.page-title', popover: { title: 'Lista de Tipos de Dano', description: 'Aqui você gerencia o catálogo de tipos de dano utilizados no check-in e checkout das reservas. Cada tipo pertence a uma categoria e tem uma localização (interior ou exterior).' } },
        { element: 'a[href*="dano/create"], .btn-create-resource', popover: { title: 'Adicionar tipo de dano', description: 'Clique aqui para cadastrar um novo tipo de dano informando o nome, a categoria e a localização no veículo.', side: 'bottom' } },
        { element: 'table thead tr', popover: { title: 'Tabela de tipos de dano', description: 'Lista todos os tipos de dano com nome, categoria, localização (interior/exterior) e status (ativo/inativo).', side: 'bottom' } },
        { element: 'table tbody tr:first-child td:nth-child(3)', popover: { title: 'Localização do dano', description: '<b>Exterior</b> (badge escuro) — danos visíveis na carroceria. <b>Interior</b> (badge âmbar) — danos no habitáculo e componentes internos.', side: 'right' } },
        { element: 'table tbody tr:first-child td:nth-child(4)', popover: { title: 'Status', description: 'Tipos <b>Ativos</b> (verde) aparecem nas opções de registro de dano durante o check-in e checkout. Tipos <b>Inativos</b> (vermelho) ficam ocultos.', side: 'right' } },
        { element: 'table tbody tr:first-child td:last-child', popover: { title: 'Ações', description: 'Use <b>Editar</b> para alterar nome, categoria ou localização. Use <b>Excluir</b> para remover o tipo (somente se não estiver vinculado a danos registrados).', side: 'left' } },
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

@section('css')
@stop


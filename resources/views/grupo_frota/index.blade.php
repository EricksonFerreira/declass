@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('grupo_frota.create')" />
@stop

@section('css')
    <style>
        .grupo-frota-thumb {
            width: 90px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #dbe4f0;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease;
            background: #f8fafc;
            display: inline-block;
        }

        .grupo-frota-thumb:hover {
            transform: scale(1.04);
            box-shadow: 0 6px 18px rgba(15, 39, 64, 0.16);
        }

        .grupo-frota-thumb--empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: .75rem;
            text-align: center;
            padding: 6px;
        }

        .modal-imagem-grupo-frota {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #dbe4f0;
        }

        .modal-imagem-grupo-frota .modal-header {
            background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 100%);
            border-bottom: 1px solid #d8e2ef;
        }

        .modal-imagem-grupo-frota .modal-body {
            background: #f8fafc;
            padding: 1rem;
        }

        .imagem-expandida-grupo-frota {
            max-height: 75vh;
            width: auto;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 39, 64, 0.18);
        }
    </style>
@stop
@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2 card-grupo-frota-index">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $grupoFrotas->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('grupo_frota.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $grupoFrotas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm table-grupo-frota-index">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Grupo</th>
                        <th>Codigos</th>
                        <th>Valor Diario</th>
                        <th>Quantidades</th>
                        <th>Status</th>
                        <th style="width: 200px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grupoFrotas as $grupo_frota)
                        @php
                            $imagemUrl = null;

                            if (!empty($grupo_frota->imagem)) {
                                if (
                                    \Illuminate\Support\Str::startsWith($grupo_frota->imagem, ['http://', 'https://'])
                                ) {
                                    $imagemUrl = $grupo_frota->imagem;
                                } elseif (
                                    \Illuminate\Support\Facades\Storage::disk('public')->exists($grupo_frota->imagem)
                                ) {
                                    $imagemUrl = \Illuminate\Support\Facades\Storage::url($grupo_frota->imagem);
                                } elseif (file_exists(public_path($grupo_frota->imagem))) {
                                    $imagemUrl = asset($grupo_frota->imagem);
                                }
                            }
                        @endphp

                        <tr>
                            <td class="text-center">
                                @if ($imagemUrl)
                                    <img src="{{ $imagemUrl }}" alt="{{ $grupo_frota->nome }}"
                                        class="grupo-frota-thumb js-expandir-imagem" data-src="{{ $imagemUrl }}"
                                        data-title="{{ $grupo_frota->nome }}">
                                @else
                                    <div class="grupo-frota-thumb grupo-frota-thumb--empty">
                                        <span>Sem imagem</span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="grupo-nome text-bold">{{ $grupo_frota->nome }}</div>
                                <small class="text-muted">ID: {{ $grupo_frota->id }}</small>
                            </td>

                            <td>
                                <div><strong>Interno:</strong> {{ $grupo_frota->codigo ?? 'Nao definido' }}</div>
                                <div><strong>SIPP:</strong> {{ $grupo_frota->codigo_sipp ?? 'Nao definido' }}</div>
                            </td>

                            <td>
                                <span
                                    class="badge badge-light border text-monospace">{{ $grupo_frota->valor_diario }}</span>
                            </td>

                            <td>
                                <div class="quantidades-coluna">
                                    <div class="mt-1">
                                        <span class="badge badge-pill badge-resumo badge-resumo-caracteristica">
                                            {{ (int) ($grupo_frota->caracteristicas_count ?? 0) }}
                                        </span>
                                        <small class="text-muted ml-1">Caracteristicas</small>
                                    </div>
                                    <div>
                                        <span class="badge badge-pill badge-resumo badge-resumo-opcional">
                                            {{ (int) ($grupo_frota->opcionais_count ?? 0) }}
                                        </span>
                                        <small class="text-muted ml-1">Opcionais</small>
                                    </div>


                                    <div class="mt-1">
                                        <span class="badge badge-pill badge-resumo badge-resumo-modelo">
                                            {{ (int) ($grupo_frota->modelos_count ?? 0) }}
                                        </span>
                                        <small class="text-muted ml-1">Modelos</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-xs bg-{{ $grupo_frota->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $grupo_frota->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>

                            <td style="width: 260px;">
                                <button type="button" class="btn btn-xs btn-info" data-toggle="modal"
                                    data-target="#modalDetalhesGrupoFrota{{ $grupo_frota->id }}"
                                    title="Visualizar detalhes">
                                    <i class="fa fa-list-ul"></i> Detalhes
                                </button>
                                <a href="{{ route('grupo_frota.edit', $grupo_frota->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('grupo_frota.destroy', $grupo_frota->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($grupoFrotas as $grupo_frota)
        <div class="modal fade" id="modalDetalhesGrupoFrota{{ $grupo_frota->id }}" tabindex="-1" role="dialog"
            aria-labelledby="modalDetalhesGrupoFrotaLabel{{ $grupo_frota->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content modal-grupo-frota-detalhes">
                    <div class="modal-header grupo-frota-modal__header">
                        <div class="grupo-frota-modal__header-left">
                            <div class="grupo-frota-modal__header-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <div class="grupo-frota-modal__eyebrow">Detalhes do grupo</div>
                                <h5 class="modal-title grupo-frota-modal__title"
                                    id="modalDetalhesGrupoFrotaLabel{{ $grupo_frota->id }}">
                                    {{ $grupo_frota->nome }}
                                </h5>
                            </div>
                        </div>
                        <button type="button" class="grupo-frota-modal__close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body grupo-frota-modal__body">
                        <div class="detalhes-area-card detalhes-area-card--hero">
                            <div class="detalhes-area-hero">
                                <div>
                                    <div class="detalhes-area-hero__label">Grupo</div>
                                    <div class="detalhes-area-hero__value">{{ $grupo_frota->nome }}</div>
                                </div>
                                <div class="detalhes-area-hero__meta">
                                    <span class="badge badge-light border">ID #{{ $grupo_frota->id }}</span>
                                    <span class="badge badge-{{ $grupo_frota->ativo == 'S' ? 'success' : 'danger' }}">
                                        {{ $grupo_frota->ativo == 'S' ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="detalhes-area-card">
                            <div class="detalhes-area-title">
                                <i class="fa fa-tasks mr-1"></i> Caracteristicas
                                <span class="badge badge-resumo badge-resumo-caracteristica ml-2">
                                    {{ (int) ($grupo_frota->caracteristicas_count ?? 0) }}
                                </span>
                            </div>
                            @if (($grupo_frota->caracteristicas_count ?? 0) > 0)
                                <ul class="detalhes-lista">
                                    @foreach ($grupo_frota->caracteristicas->sortBy('nome') as $caracteristica)
                                        <li>{{ $caracteristica->nome }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted text-sm">Sem caracteristicas vinculadas.</div>
                            @endif
                        </div>

                        <div class="detalhes-area-card">
                            <div class="detalhes-area-title">
                                <i class="fa fa-plus-circle mr-1"></i> Opcionais
                                <span class="badge badge-resumo badge-resumo-opcional ml-2">
                                    {{ (int) ($grupo_frota->opcionais_count ?? 0) }}
                                </span>
                            </div>
                            @if (($grupo_frota->opcionais_count ?? 0) > 0)
                                @php
                                    $opcionaisAgrupados = $grupo_frota->opcionais
                                        ->sortBy('nome')
                                        ->groupBy(function ($opcional) {
                                            return optional($opcional->tipoOpcional)->nome ?? 'Sem tipo';
                                        });
                                @endphp
                                @foreach ($opcionaisAgrupados as $tipoOpcionalNome => $opcionaisTipo)
                                    <div class="detalhes-subtitulo">{{ $tipoOpcionalNome }}</div>
                                    <ul class="detalhes-lista detalhes-lista--compacta">
                                        @foreach ($opcionaisTipo as $opcional)
                                            <li>{{ $opcional->nome }}</li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            @else
                                <div class="text-muted text-sm">Sem opcionais vinculados.</div>
                            @endif
                        </div>

                        <div class="detalhes-area-card mb-0">
                            <div class="detalhes-area-title">
                                <i class="fa fa-car mr-1"></i> Modelos
                                <span class="badge badge-resumo badge-resumo-modelo ml-2">
                                    {{ (int) ($grupo_frota->modelos_count ?? 0) }}
                                </span>
                            </div>
                            @php
                                $imagensExtras = $grupo_frota->imagens_urls ?? [];
                                $imagemPrincipal = $grupo_frota->imagem_url ?? null;
                                if ($imagemPrincipal) {
                                    $imagensExtras = array_values(array_filter($imagensExtras, fn ($img) => $img !== $imagemPrincipal));
                                }
                            @endphp
                            @if(count($imagensExtras) > 0)
                                <div class="detalhes-subtitulo">Imagens adicionais</div>
                                <div class="grupo-frota-imagens-extra">
                                    @foreach($imagensExtras as $extra)
                                        <button type="button" class="btn btn-link p-0 js-expandir-imagem"
                                                data-src="{{ $extra }}" data-title="{{ $grupo_frota->nome }}">
                                            <img src="{{ $extra }}" alt="Imagem {{ $grupo_frota->nome }}"
                                                 class="grupo-frota-thumb grupo-frota-thumb--mini">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @if (($grupo_frota->modelos_count ?? 0) > 0)
                                <ul class="detalhes-lista">
                                    @foreach ($grupo_frota->modelos->sortBy('nome') as $modelo)
                                        <li>{{ $modelo->nome }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted text-sm">Sem modelos vinculados.</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer grupo-frota-modal__footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="modal fade" id="modalImagemGrupoFrota" tabindex="-1" role="dialog"
        aria-labelledby="modalImagemGrupoFrotaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content modal-imagem-grupo-frota">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImagemGrupoFrotaLabel">Imagem do grupo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagemExpandidaGrupoFrota" src="" alt=""
                        class="img-fluid imagem-expandida-grupo-frota">
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-grupo-frota-index td {
            vertical-align: middle;
        }

        .grupo-nome {
            color: #0f2740;
        }

        .table-grupo-frota-index .badge-light {
            color: #334155;
            background: #f8fafc;
            font-weight: 600;
        }

        .badge-resumo {
            min-width: 34px;
            font-weight: 700;
            font-size: .78rem;
            border: 1px solid transparent;
        }

        .badge-resumo-opcional {
            background: #eaf6ff;
            color: #0b5ed7;
            border-color: #b7dcff;
        }

        .badge-resumo-caracteristica {
            background: #ecfdf3;
            color: #198754;
            border-color: #b8e8cf;
        }

        .badge-resumo-modelo {
            background: #fff7e8;
            color: #b36b00;
            border-color: #ffe0ad;
        }

        .modal-grupo-frota-detalhes {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #d6dfeb;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .20);
        }

        .grupo-frota-modal__header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);
            border: 0;
            padding: 18px 22px;
            color: #fff;
            align-items: flex-start;
        }

        .grupo-frota-modal__header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .grupo-frota-modal__header-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .grupo-frota-modal__eyebrow {
            font-size: .68rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .68);
            margin-bottom: 4px;
            font-weight: 700;
        }

        .grupo-frota-modal__title {
            font-size: 1.15rem;
            font-weight: 800;
            line-height: 1.2;
            margin: 0;
            color: #fff;
        }

        .grupo-frota-modal__close {
            appearance: none;
            border: 0;
            background: rgba(255, 255, 255, .1);
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s ease, transform .15s ease;
        }

        .grupo-frota-modal__close:hover {
            background: rgba(255, 255, 255, .18);
            transform: scale(1.03);
        }

        .grupo-frota-modal__body {
            background: linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
            padding: 20px;
        }

        .grupo-frota-modal__footer {
            background: #f8fbff;
            border-top: 1px solid #d8e2ef;
            padding: 14px 18px;
        }

        .detalhes-area-card {
            border: 1px solid #d9e5f1;
            border-radius: 16px;
            padding: 16px 18px;
            background: #fff;
            margin-bottom: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        }

        .detalhes-area-card--hero {
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border-color: #cfe0f2;
        }

        .detalhes-area-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #22334a;
            margin-bottom: 10px;
            font-size: .92rem;
        }

        .detalhes-area-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .detalhes-area-hero__label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .detalhes-area-hero__value {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .detalhes-area-hero__meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .detalhes-subtitulo {
            font-weight: 600;
            color: #334155;
            margin: 10px 0 6px;
            font-size: .82rem;
        }

        .detalhes-lista {
            margin-bottom: 0;
            padding-left: 18px;
        }

        .detalhes-lista li {
            font-size: .86rem;
            color: #1f2937;
            margin-bottom: 4px;
            line-height: 1.45;
        }

        .detalhes-lista--compacta {
            margin-bottom: 6px;
        }

        .detalhes-subtitulo + .detalhes-lista,
        .detalhes-area-title + .detalhes-lista {
            margin-top: 6px;
        }

        .grupo-frota-imagens-extra {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }

        .grupo-frota-thumb--mini {
            width: 68px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #dbe6f3;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .08);
        }

        @media (max-width: 991.98px) {
            .table-grupo-frota-index {
                min-width: 1120px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        $(document).on('click', '.js-expandir-imagem', function () {
            var src = $(this).attr('data-src');
            var title = $(this).attr('data-title') || 'Imagem do grupo';

            $('#imagemExpandidaGrupoFrota').attr('src', src);
            $('#imagemExpandidaGrupoFrota').attr('alt', title);
            $('#modalImagemGrupoFrotaLabel').text(title);
            $('#modalImagemGrupoFrota').modal('show');
        });
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'grupo-frota-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
{ element: '.page-title', popover: { title: 'Lista de Grupos de Frota', description: 'Aqui você gerencia os grupos de frota (categorias de veículos). Cada grupo agrupa modelos similares, define preço diário, características, opcionais e imagens usados nas reservas.' } },
        { element: '.btn-create-resource, a[href*="grupo-frota/create"], a[href*="grupo_frota/create"]', popover: { title: 'Adicionar grupo de frota', description: 'Clique aqui para criar um novo grupo. Você passará pelas abas: <b>Geral</b> (nome, código, preço), <b>Características</b>, <b>Opcionais</b> e <b>Modelos</b> vinculados.', side: 'bottom' } },
        { element: '.table-grupo-frota-index thead tr', popover: { title: 'Tabela de grupos', description: 'Exibe imagem de capa, nome, código SIPP, valor diário, quantidade de modelos e características vinculadas, e status.', side: 'bottom' } },
        { element: '.grupo-frota-thumb', popover: { title: 'Imagem do grupo', description: 'Foto de capa do grupo exibida na listagem de reservas. Clique na miniatura para ampliar. Grupos sem imagem exibem um ícone padrão.', side: 'right' } },
        { element: '.badge-resumo', popover: { title: 'Resumo de vínculos', description: 'Badges indicam quantos <b>modelos</b>, <b>características</b> e <b>opcionais</b> estão vinculados ao grupo. Clique no ícone de detalhes para ver a lista completa.', side: 'left' } },
        { element: '.table-grupo-frota-index tbody tr:first-child td:last-child', popover: { title: 'Ações', description: 'Use <b>Detalhes</b> para ver tudo vinculado ao grupo, <b>Editar</b> para alterar dados e <b>Excluir</b> para remover (somente se não houver reservas vinculadas).', side: 'left' } },
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


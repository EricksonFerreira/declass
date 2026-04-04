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
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDetalhesGrupoFrotaLabel{{ $grupo_frota->id }}">
                            Detalhes do Grupo: {{ $grupo_frota->nome }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #dbe4f0;
        }

        .modal-grupo-frota-detalhes .modal-header {
            background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 100%);
            border-bottom: 1px solid #d8e2ef;
        }

        .detalhes-area-card {
            border: 1px solid #dbe6f3;
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            margin-bottom: 10px;
        }

        .detalhes-area-title {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: #22334a;
            margin-bottom: 8px;
            font-size: .9rem;
        }

        .detalhes-subtitulo {
            font-weight: 600;
            color: #334155;
            margin: 8px 0 4px;
            font-size: .82rem;
        }

        .detalhes-lista {
            margin-bottom: 0;
            padding-left: 18px;
        }

        .detalhes-lista li {
            font-size: .84rem;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .detalhes-lista--compacta {
            margin-bottom: 6px;
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
@stop
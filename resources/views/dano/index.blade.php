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
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-success">{{ $danos->total() }}</span>
                </h3>
                <div class="align-bottom d-flex flex-row justify-between" style="gap:.75rem;">
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

        <div class="card-body px-0 pt-0 pb-0">
            <div class="dano-grid">
                @forelse ($danos as $dano)
                    <div class="dano-card"
                         data-dano-id="{{ $dano->id }}"
                         data-dano-nome="{{ e($dano->nome) }}"
                         data-dano-ativo="{{ $dano->ativo }}"
                         data-dano-categoria-id="{{ $dano->categoria_id }}"
                         data-dano-localizacao="{{ $dano->localizacao }}"
                         data-dano-update-url="{{ route('dano.update', $dano->id) }}">
                        <div class="dano-card__top">
                            <div>
                                <div class="dano-card__title">{{ $dano->nome }}</div>
                                <div class="dano-card__meta">ID {{ $dano->id }}</div>
                            </div>
                            <span class="badge badge-xs bg-{{ $dano->ativo == 'S' ? 'success' : 'danger' }}">
                                {{ $dano->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                            </span>
                        </div>

                        <div class="dano-card__body">
                            <div class="dano-card__line"><strong>Categoria:</strong> {{ $dano->categoria?->nome ?? '-' }}</div>
                            <div class="dano-card__line"><strong>Localização:</strong> {{ ucfirst((string) $dano->localizacao) }}</div>
                        </div>

                        <div class="dano-card__actions">
                            <button type="button" class="btn btn-xs btn-warning btn-edit-dano">
                                <i class="fa fa-edit"></i> Editar
                            </button>
                            <x-delete-button
                                :action="route('dano.destroy', $dano->id)"
                                titulo="Excluir"
                                tituloMensagem="Excluir dano?"
                                mensagem="Tem certeza que deseja excluir este dano?"
                                textoConfirmacao="Sim, excluir"
                                btnClass="btn btn-xs btn-danger"
                            />
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border text-muted mb-0">
                            Nenhum dano cadastrado.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-dano" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar dano</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-edit-dano" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <x-form-input type="text" class="form-control form-control-sm" id="modal_dano_nome" name="nome" label="Nome" value="" required />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <x-form-select id="modal_dano_categoria_id" name="categoria_id" label="Categoria" :options="$categorias" placeholder="Selecione uma categoria" :selected="null" class="form-control form-control-sm" required />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <x-form-select id="modal_dano_localizacao" name="localizacao" label="Localização" :options="['interior' => 'Interior', 'exterior' => 'Exterior']" placeholder="Selecione uma localização" :selected="null" class="form-control form-control-sm" required />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <x-form-select id="modal_dano_ativo" name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" placeholder="Selecione um valor" :selected="null" class="form-control form-control-sm" required />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .dano-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: .85rem;
            padding: .75rem;
        }

        .dano-card {
            background: #fff;
            border: 1px solid #dde5ef;
            border-radius: 14px;
            padding: .9rem;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .04);
            display: flex;
            flex-direction: column;
            gap: .75rem;
            min-height: 170px;
            cursor: pointer;
            transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        .dano-card:hover {
            transform: translateY(-2px);
            border-color: #4b80ff;
            box-shadow: 0 10px 20px rgba(75, 128, 255, .08);
        }

        .dano-card__top {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            align-items: flex-start;
        }

        .dano-card__title {
            font-size: .98rem;
            font-weight: 700;
            color: #0f2740;
            line-height: 1.25;
        }

        .dano-card__meta {
            font-size: .78rem;
            color: #64748b;
            margin-top: .15rem;
        }

        .dano-card__body {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: .45rem;
            justify-content: center;
        }

        .dano-card__line {
            font-size: .86rem;
            color: #334155;
        }

        .dano-card__line strong {
            color: #0f2740;
        }

        .dano-card__actions {
            display: flex;
            gap: .45rem;
            flex-wrap: wrap;
            align-items: center;
        }

        @media (max-width: 1399.98px) {
            .dano-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        @media (max-width: 1199.98px) {
            .dano-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 991.98px) {
            .dano-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 575.98px) {
            .dano-grid {
                grid-template-columns: 1fr;
                padding: .5rem;
            }

            .dano-card {
                min-height: unset;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('click', function (event) {
            if (event.target.closest('.delete-button') || event.target.closest('.delete-form')) {
                return;
            }

            const card = event.target.closest('.dano-card');
            if (!card) {
                return;
            }

            const modal = $('#modal-edit-dano');
            const form = document.getElementById('form-edit-dano');
            if (!modal.length || !form) {
                return;
            }

            form.action = card.dataset.danoUpdateUrl || form.action;
            document.getElementById('modal_dano_nome').value = card.dataset.danoNome || '';
            document.getElementById('modal_dano_categoria_id').value = card.dataset.danoCategoriaId || '';
            document.getElementById('modal_dano_localizacao').value = card.dataset.danoLocalizacao || '';
            document.getElementById('modal_dano_ativo').value = card.dataset.danoAtivo || 'S';
            modal.modal('show');
        });
    </script>
@stop

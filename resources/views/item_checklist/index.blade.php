@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('item_checklist.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-success">{{ $item_checklists->total() }}</span>
                </h3>
                <div class="align-bottom d-flex flex-row justify-between" style="gap:.75rem;">
                    <form action="{{ route('item_checklist.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $item_checklists->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-0">
            <div class="item-checklist-grid">
                @forelse ($item_checklists as $item_checklist)
                    <div class="item-checklist-card"
                         data-item-id="{{ $item_checklist->id }}"
                         data-item-nome="{{ e($item_checklist->nome) }}"
                         data-item-ativo="{{ $item_checklist->ativo }}"
                         data-item-update-url="{{ route('item_checklist.update', $item_checklist->id) }}">
                        <div class="item-checklist-card__top">
                            <div>
                                <div class="item-checklist-card__title">{{ $item_checklist->nome }}</div>
                                <div class="item-checklist-card__meta">ID {{ $item_checklist->id }}</div>
                            </div>
                            <span class="badge badge-xs bg-{{ $item_checklist->ativo == 'S' ? 'success' : 'danger' }}">
                                {{ $item_checklist->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                            </span>
                        </div>

                        <div class="item-checklist-card__body">
                            <div class="item-checklist-card__hint">
                                Clique no card para editar nome ou status.
                            </div>
                        </div>

                        <div class="item-checklist-card__actions">
                            <button type="button" class="btn btn-xs btn-warning btn-edit-item-checklist">
                                <i class="fa fa-edit"></i> Editar
                            </button>
                            <x-delete-button
                                :action="route('item_checklist.destroy', $item_checklist->id)"
                                titulo="Excluir"
                                tituloMensagem="Excluir item de checklist?"
                                mensagem="Tem certeza que deseja excluir este item?"
                                textoConfirmacao="Sim, excluir"
                                btnClass="btn btn-xs btn-danger"
                            />
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border text-muted mb-0">
                            Nenhum item de checklist cadastrado.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-item-checklist" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar item de checklist</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-edit-item-checklist" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <x-form-input
                                        type="text"
                                        class="form-control form-control-sm"
                                        id="modal_item_nome"
                                        name="nome"
                                        label="Nome"
                                        value=""
                                        required
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <x-form-select
                                        id="modal_item_ativo"
                                        name="ativo"
                                        label="Status"
                                        :options="['S' => 'Ativo', 'N' => 'Inativo']"
                                        placeholder="Selecione um valor"
                                        :selected="null"
                                        class="form-control form-control-sm"
                                        required
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="text-muted small">
                            Ajuste o nome ou o status sem sair da lista.
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
        .item-checklist-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: .85rem;
            padding: .75rem;
        }

        .item-checklist-card {
            background: #fff;
            border: 1px solid #dde5ef;
            border-radius: 14px;
            padding: .9rem;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .04);
            display: flex;
            flex-direction: column;
            gap: .8rem;
            min-height: 160px;
            cursor: pointer;
            transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        .item-checklist-card:hover {
            transform: translateY(-2px);
            border-color: #4b80ff;
            box-shadow: 0 10px 20px rgba(75, 128, 255, .08);
        }

        .item-checklist-card__top {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            align-items: flex-start;
        }

        .item-checklist-card__title {
            font-size: .98rem;
            font-weight: 700;
            color: #0f2740;
            line-height: 1.25;
        }

        .item-checklist-card__meta {
            font-size: .78rem;
            color: #64748b;
            margin-top: .15rem;
        }

        .item-checklist-card__body {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .item-checklist-card__hint {
            font-size: .85rem;
            color: #64748b;
            line-height: 1.35;
        }

        .item-checklist-card__actions {
            display: flex;
            gap: .45rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
        }

        @media (max-width: 1399.98px) {
            .item-checklist-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1199.98px) {
            .item-checklist-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .item-checklist-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .item-checklist-grid {
                grid-template-columns: 1fr;
                padding: .5rem;
            }

            .item-checklist-card {
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

            const card = event.target.closest('.item-checklist-card');

            if (!card) {
                return;
            }

            const modal = $('#modal-edit-item-checklist');
            const form = document.getElementById('form-edit-item-checklist');

            if (!modal.length || !form) {
                return;
            }

            form.action = card.dataset.itemUpdateUrl || form.action;
            document.getElementById('modal_item_nome').value = card.dataset.itemNome || '';
            document.getElementById('modal_item_ativo').value = card.dataset.itemAtivo || 'S';

            modal.modal('show');
        });
    </script>
@stop

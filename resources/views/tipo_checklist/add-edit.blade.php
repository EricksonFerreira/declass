@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $itensSelecionados = array_map('strval', old('item_checklist_id', isset($tipo_checklist) ? $tipo_checklist->configuracaoChecklist->pluck('item_checklist_id')->map(fn ($id) => (string) $id)->toArray() : []));
        $qtdItens = is_countable($itemChecklist ?? null) ? count($itemChecklist) : 0;
        $qtdSelecionados = count($itensSelecionados);
        $statusAtual = old('ativo', $tipo_checklist->ativo ?? 'S');
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

    <div class="col-12 px-0">
        <div class="card card-tipo-checklist shadow-sm border-0">
            <div class="card-header card-header-tipo-checklist border-0">
                <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:1rem;">
                    <div>
                        <h4 class="mb-1">{{ $titulo }}</h4>
                        <p class="mb-0 text-muted">Cadastre o tipo e selecione abaixo os itens que fazem parte dele.</p>
                    </div>
                    <div class="tipo-checklist-summary">
                        <span class="badge badge-light">{{ $qtdItens }} itens cadastrados</span>
                        <span class="badge badge-primary">{{ $qtdSelecionados }} selecionados</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-tipo-checklist" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="tipo-checklist-section-title">Dados do tipo</div>
                            <div class="form-row">
                                <div class="col-md-8">
                                    <div class="form-group form-group-tipo-checklist">
                                        <x-form-input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="nome"
                                            name="nome"
                                            label="Nome do tipo de checklist"
                                            value="{{ old('nome', $tipo_checklist->nome ?? '') }}"
                                            placeholder="Ex.: Retirada, Devolução, Manutenção"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group form-group-tipo-checklist">
                                        <x-form-select
                                            name="ativo"
                                            label="Status"
                                            :options="$status"
                                            placeholder="Selecione um valor"
                                            :selected="$statusAtual"
                                            class="form-control form-control-sm"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="tipo-checklist-section-title mt-3">Itens do tipo</div>
                            <div class="checklist-actions mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="alterarTodosItens(true)">
                                    <i class="fa fa-check mr-1"></i> Selecionar todos
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="alterarTodosItens(false)">
                                    <i class="fa fa-times mr-1"></i> Limpar seleção
                                </button>
                            </div>

                            <div class="checklist-items-grid">
                                @forelse ($itemChecklist as $key => $value)
                                    @php
                                        $itemId = (string) $key;
                                        $marcado = in_array($itemId, $itensSelecionados, true);
                                    @endphp

                                    <label class="checklist-item-card" for="item_checklist_{{ $key }}">
                                        <input
                                            type="checkbox"
                                            class="checklist-item-card__checkbox"
                                            id="item_checklist_{{ $key }}"
                                            name="item_checklist_id[]"
                                            value="{{ $key }}"
                                            {{ $marcado ? 'checked' : '' }}
                                        >
                                        <span class="checklist-item-card__label">{{ $value }}</span>
                                    </label>
                                @empty
                                    <div class="empty-items-state">
                                        Nenhum item de checklist cadastrado ainda.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-lg-5 mt-4 mt-lg-0">
                            <div class="tipo-checklist-sidecard">
                                <div class="tipo-checklist-section-title">Resumo</div>
                                <div class="summary-line"><span>Nome</span><strong>{{ old('nome', $tipo_checklist->nome ?? 'Novo tipo') }}</strong></div>
                                <div class="summary-line"><span>Status</span><strong>{{ $statusAtual === 'S' ? 'Ativo' : 'Inativo' }}</strong></div>
                                <div class="summary-line"><span>Itens selecionados</span><strong>{{ $qtdSelecionados }}</strong></div>
                                <div class="summary-note">
                                    Os itens marcados serão exibidos nos fluxos de checklist vinculados a este tipo.
                                </div>

                                <button type="submit" class="btn btn-primary btn-save-tipo-checklist w-100 mt-3">
                                    Salvar tipo
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-tipo-checklist {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-tipo-checklist {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-tipo-checklist h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .tipo-checklist-summary {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .tipo-checklist-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-tipo-checklist {
        margin-bottom: 1rem;
    }

    .card-tipo-checklist .form-control,
    .card-tipo-checklist .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-tipo-checklist .form-control:focus,
    .card-tipo-checklist .form-select:focus {
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
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
    }

    .checklist-item-card {
        margin: 0;
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: .6rem .7rem;
        border: 1px solid #d8e1ec;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: all .15s ease;
        min-height: 48px;
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

    .tipo-checklist-sidecard {
        background: #fff;
        border: 1px solid #e5eaf2;
        border-radius: 14px;
        padding: 1rem;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .05);
        position: sticky;
        top: 1rem;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        padding: .55rem 0;
        border-bottom: 1px dashed #edf2f7;
        font-size: .92rem;
    }

    .summary-line span {
        color: #64748b;
    }

    .summary-line strong {
        color: #0f2740;
        text-align: right;
    }

    .summary-note {
        margin-top: .85rem;
        font-size: .85rem;
        color: #64748b;
        line-height: 1.45;
    }

    .empty-items-state {
        grid-column: 1 / -1;
        padding: 1rem;
        border: 1px dashed #d8e1ec;
        border-radius: 10px;
        text-align: center;
        color: #64748b;
        background: #fbfdff;
    }

    .btn-save-tipo-checklist {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-tipo-checklist {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .tipo-checklist-sidecard {
            position: static;
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

        .card-header-tipo-checklist h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-tipo-checklist p {
            font-size: .85rem;
        }

        .tipo-checklist-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-tipo-checklist {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
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
@stop

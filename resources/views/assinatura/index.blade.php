@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo" :createUrl="route('assinatura.atribuir')" />
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="assinatura-filter-sticky">
        <div class="card card-outline card-light mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="mb-1">Empresa</label>
                        <input type="text" name="empresa" value="{{ request('empresa') }}" class="form-control form-control-sm" placeholder="Nome da empresa">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="mb-1">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach (['ativo', 'trial', 'inadimplente', 'aguardando_pagamento', 'expirado', 'cancelado', 'arquivada'] as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="mb-1">Plano</label>
                        <select name="plano_id" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach ($planos as $p)
                                <option value="{{ $p->id }}" {{ request('plano_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex" style="gap:8px;">
                        <button type="submit" class="btn btn-secondary btn-sm flex-grow-1">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('assinatura.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-success">{{ $assinaturas->total() }}</span>
                </h3>

                <div class="d-flex flex-row align-items-center justify-content-end" style="gap:6px;flex-wrap:wrap;">
                    <a href="{{ route('assinatura.atribuir') }}" class="btn btn-xs btn-primary">
                        <i class="fas fa-link mr-1"></i> Atribuir
                    </a>

                    <a href="{{ route('assinatura.index') }}" class="btn btn-xs btn-outline-secondary">
                        <i class="fas fa-sync mr-1"></i> Atualizar
                    </a>

                    <div class="d-flex align-items-center">
                        {{ $assinaturas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 assinatura-index-body">
            <div class="assinatura-cards">
                @forelse ($assinaturas as $ass)
                    @php
                        $badge = $ass->status_badge;
                        $dias = $ass->diasParaVencer();
                        $storageMb = $ass->plano?->limite_armazenamento_mb ?? 0;
                        $storageTxt = $storageMb >= 1024
                            ? number_format($storageMb / 1024, 1, ',', '.') . ' GB'
                            : $storageMb . ' MB';

                        $nomeEmpresa = (string) ($ass->empresa->nome ?? 'Empresa excluída');
                        $iniciais = collect(preg_split('/\s+/', trim($nomeEmpresa)) ?: [])
                            ->filter()
                            ->take(2)
                            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                            ->implode('');
                        $iniciais = $iniciais ?: 'AS';
                    @endphp

                    <article class="sa-card" onclick="window.location='{{ route('assinatura.show', $ass) }}';">
                        <div class="sa-card__accent"></div>

                        <div class="sa-card__head">
                            <div class="sa-card__avatar">{{ $iniciais }}</div>

                            <div class="sa-card__head-info">
                                <div class="sa-card__id">ASSINATURA #{{ $ass->id }}</div>
                                <div class="sa-card__title">{{ $nomeEmpresa }}</div>
                                <div class="sa-card__sub">
                                    {{ $ass->plano?->nome ?? 'Sem plano' }}@if ($ass->plano?->destaque)
                                        <i class="fas fa-star text-warning ml-1" style="font-size:.7rem;"></i>
                                    @endif
                                </div>
                                <div class="sa-card__hint">
                                    <i class="fas fa-hand-pointer"></i> Clique no card para ver a assinatura
                                </div>
                            </div>

                            <div class="sa-card__badges">
                                <span class="sa-card__badge {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>

                                @if ($dias <= 10 && $dias >= 0 && $ass->estaAtivo())
                                    <span class="sa-card__badge sa-card__badge--amber">
                                        Vence em {{ $dias }} dia(s)
                                    </span>
                                @elseif ($dias < 0 && $ass->status !== 'cancelado')
                                    <span class="sa-card__badge sa-card__badge--red">
                                        {{ abs($dias) }} dia(s) em atraso
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sa-card__meta">
                            <div class="sa-card__meta-row">
                                <span class="sa-card__meta-label"><i class="fas fa-calendar-alt mr-1"></i>Periodicidade</span>
                                <strong class="sa-card__meta-value">{{ $ass->periodicidade_texto }}</strong>
                            </div>

                            <div class="sa-card__meta-row">
                                <span class="sa-card__meta-label"><i class="fas fa-money-bill-wave mr-1"></i>Valor</span>
                                <strong class="sa-card__meta-value">R$ {{ number_format($ass->valor_cobrado, 2, ',', '.') }}</strong>
                            </div>

                            <div class="sa-card__meta-row">
                                <span class="sa-card__meta-label"><i class="fas fa-credit-card mr-1"></i>Cobrança</span>
                                <strong class="sa-card__meta-value text-capitalize">{{ str_replace('_', ' ', $ass->metodo_cobranca) }}</strong>
                            </div>

                            <div class="sa-card__meta-row">
                                <span class="sa-card__meta-label"><i class="fas fa-hdd mr-1"></i>Armazenamento</span>
                                <strong class="sa-card__meta-value">{{ $storageTxt }}</strong>
                            </div>

                            <div class="sa-card__meta-row sa-card__meta-row--full">
                                <span class="sa-card__meta-label"><i class="fas fa-clock mr-1"></i>Vigência</span>
                                <strong class="sa-card__meta-value">
                                    Início {{ $ass->data_inicio->format('d/m/Y') }} · Vencimento {{ $ass->data_vencimento->format('d/m/Y') }}
                                </strong>
                            </div>
                        </div>

                        <div class="sa-card__foot">
                            <div class="d-flex flex-wrap" style="gap:6px;">
                                <button
                                    type="button"
                                    class="btn btn-xs btn-warning text-dark"
                                    onclick="event.stopPropagation(); abrirEdicaoAssinatura(this)"
                                    data-assinatura-id="{{ $ass->id }}"
                                    data-empresa-id="{{ $ass->empresa?->id }}"
                                    data-plano-id="{{ $ass->plano_id }}"
                                    data-periodicidade="{{ $ass->periodicidade }}"
                                    data-status="{{ $ass->status }}"
                                    data-metodo-cobranca="{{ $ass->metodo_cobranca }}"
                                    data-data-inicio="{{ optional($ass->data_inicio)->format('Y-m-d') }}"
                                    data-data-vencimento="{{ optional($ass->data_vencimento)->format('Y-m-d') }}"
                                    data-dia-vencimento="{{ $ass->dia_vencimento }}"
                                    data-valor-cobrado="{{ $ass->valor_cobrado }}"
                                >
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </button>
                                <form action="{{ route('assinatura.destroy', $ass) }}" method="POST" class="d-inline delete-form" onclick="event.stopPropagation();">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        class="btn btn-xs btn-danger delete-button"
                                        onclick="event.stopPropagation(); confirmarRemocaoAssinatura(this)"
                                        data-confirm-title="Remover assinatura?"
                                        data-confirm-message="A assinatura da empresa {{ $nomeEmpresa }} será removida. Essa ação pode afetar os pagamentos vinculados."
                                        data-confirm-button="Sim, remover"
                                        data-confirm-color="#dc3545"
                                    >
                                        <i class="fas fa-trash mr-1"></i> Remover
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="alert alert-light border text-center text-muted mb-0 py-4">
                        Nenhuma assinatura encontrada.
                    </div>
                @endforelse
            </div>
        </div>

        @if ($assinaturas->hasPages())
            <div class="card-footer py-2">
                {{ $assinaturas->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade modal-assinatura" id="modalEditarAssinatura" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" id="formEditarAssinatura">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Editar assinatura</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="mb-1">Empresa</label>
                                <select name="empresa_id" id="edit_empresa_id" class="form-control">
                                    @foreach ($assinaturas as $item)
                                        @if ($item->empresa)
                                            <option value="{{ $item->empresa->id }}">{{ $item->empresa->nome }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-1">Plano</label>
                                <select name="plano_id" id="edit_plano_id" class="form-control">
                                    @foreach ($planos as $p)
                                        <option value="{{ $p->id }}">{{ $p->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="mb-1">Periodicidade</label>
                                <select name="periodicidade" id="edit_periodicidade" class="form-control">
                                    <option value="mensal">Mensal</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="mb-1">Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="ativo">Ativo</option>
                                    <option value="trial">Trial</option>
                                    <option value="inadimplente">Inadimplente</option>
                                    <option value="aguardando_pagamento">Aguardando pagamento</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="mb-1">Método cobrança</label>
                                <select name="metodo_cobranca" id="edit_metodo_cobranca" class="form-control">
                                    <option value="pix">PIX</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="cartao">Cartão</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="transferencia">Transferência</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="mb-1">Valor</label>
                                <input type="number" step="0.01" min="0" name="valor_cobrado" id="edit_valor_cobrado" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="mb-1">Data início</label>
                                <input type="date" name="data_inicio" id="edit_data_inicio" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="mb-1">Vencimento</label>
                                <input type="date" name="data_vencimento" id="edit_data_vencimento" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="mb-1">Dia vencimento</label>
                                <input type="number" min="1" max="28" name="dia_vencimento" id="edit_dia_vencimento" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmarRemocaoAssinatura(button) {
            var form = button.closest('form.delete-form');
            if (!form) {
                return;
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: button.dataset.confirmTitle || 'Você tem certeza?',
                    text: button.dataset.confirmMessage || 'Esta ação não pode ser desfeita!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: button.dataset.confirmButton || 'Sim, excluir!',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: button.dataset.confirmColor || '#d33',
                    cancelButtonColor: '#6c757d',
                    focusCancel: true,
                }).then(function (result) {
                    if (result && result.isConfirmed) {
                        form.submit();
                    }
                });
                return;
            }

            if (window.confirm('Remover esta assinatura?')) {
                form.submit();
            }
        }

        function abrirEdicaoAssinatura(button) {
            var $button = $(button);
            var assinaturaId = $button.data('assinatura-id');

            $('#formEditarAssinatura').attr('action', '{{ url('/admin/assinatura') }}/' + assinaturaId);
            $('#edit_empresa_id').val(String($button.data('empresa-id')));
            $('#edit_plano_id').val(String($button.data('plano-id')));
            $('#edit_periodicidade').val(String($button.data('periodicidade')));
            $('#edit_status').val(String($button.data('status')));
            $('#edit_metodo_cobranca').val(String($button.data('metodo-cobranca')));
            $('#edit_data_inicio').val($button.data('data-inicio'));
            $('#edit_data_vencimento').val($button.data('data-vencimento'));
            $('#edit_dia_vencimento').val($button.data('dia-vencimento'));
            $('#edit_valor_cobrado').val($button.data('valor-cobrado'));

            $('#modalEditarAssinatura').modal('show');
        }

    </script>
@stop

@section('css')
    <style>
        .assinatura-filter-sticky {
            position: sticky;
            top: 10px;
            z-index: 1030;
        }

        .assinatura-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 16px;
            padding: 10px 8px 16px;
        }

        .sa-card {
            position: relative;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 18px;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(11, 18, 32, .06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .sa-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(11, 18, 32, .13);
        }

        .sa-card__accent {
            height: 3px;
            background: linear-gradient(90deg, #0f172a, #334155);
        }

        .sa-card__head {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .sa-card__avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
            font-size: .82rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            letter-spacing: .04em;
        }

        .sa-card__head-info {
            flex: 1;
            min-width: 0;
        }

        .sa-card__id {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .sa-card__title {
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sa-card__sub {
            font-size: .74rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .sa-card__hint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: .7rem;
            font-weight: 700;
            color: #0f766e;
            background: #ecfeff;
            border: 1px solid #99f6e4;
            border-radius: 999px;
            padding: 4px 8px;
            width: fit-content;
        }

        .sa-card__badges {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
            margin-left: 6px;
        }

        .sa-card__badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .70rem;
            font-weight: 700;
            padding: 5px 8px;
            border-radius: 999px;
            line-height: 1;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .sa-card__badge--amber {
            background: rgba(245, 158, 11, .14);
            color: #b45309;
            border-color: rgba(245, 158, 11, .28);
        }

        .sa-card__badge--red {
            background: rgba(239, 68, 68, .12);
            color: #b91c1c;
            border-color: rgba(239, 68, 68, .22);
        }

        .sa-card__meta {
            padding: 12px 16px 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 12px;
        }

        .sa-card__meta-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .sa-card__meta-row--full {
            grid-column: 1 / -1;
        }

        .sa-card__meta-label {
            font-size: .70rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .sa-card__meta-value {
            font-size: .86rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sa-card__foot {
            margin-top: auto;
            padding: 12px 16px 14px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .modal-assinatura .modal-content {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        }

        .modal-assinatura .modal-header {
            border-bottom: 0;
            padding: 18px 22px;
            color: #fff;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #2563eb 100%);
        }

        .modal-assinatura .modal-title {
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .modal-assinatura .modal-body {
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            padding: 22px;
        }

        .modal-assinatura .modal-footer {
            background: #fff;
            border-top: 1px solid #e8eef6;
            padding: 16px 22px 22px;
        }

        .modal-assinatura .form-control {
            border-radius: 12px;
            border-color: #d7e0ea;
            box-shadow: none;
        }

        .modal-assinatura .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, .12);
        }

        .modal-assinatura label {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }

        @media (max-width: 991.98px) {
            .assinatura-cards {
                grid-template-columns: 1fr;
                padding: 6px 4px 12px;
            }
        }
    </style>
@stop

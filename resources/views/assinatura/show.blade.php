@extends('adminlte::page')

@section('title', 'Assinatura — ' . $assinatura->empresa->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <div>
            <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">
                {{ $assinatura->empresa->nome }}
                <span class="badge {{ $assinatura->status_badge['class'] }} ml-2" style="font-size:.75rem;">
                    {{ $assinatura->status_badge['label'] }}
                </span>
            </h1>
            <small class="text-muted">
                Plano <strong>{{ $assinatura->plano->nome }}</strong>
                · {{ $assinatura->periodicidade_texto }}
                · R$ {{ number_format($assinatura->valor_cobrado, 2, ',', '.') }}/mês
            </small>
        </div>
        <a href="{{ route('assinatura.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
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

    <div class="row">

        {{-- ── COLUNA ESQUERDA: detalhes + controles ──────────────────────── --}}
        <div class="col-md-4">

            {{-- Detalhes da assinatura --}}
            <div class="card mb-3" style="border-left:4px solid #0a1628;">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0" style="font-size:.9rem;">
                        <i class="fas fa-id-card mr-1 text-primary"></i> Assinatura
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-borderless mb-0" style="font-size:.82rem;">
                        <tr>
                            <td class="text-muted pl-3" style="width:45%;">Plano</td>
                            <td class="font-weight-bold pr-3">{{ $assinatura->plano->nome }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted pl-3">Periodicidade</td>
                            <td class="pr-3">{{ $assinatura->periodicidade_texto }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted pl-3">Valor</td>
                            <td class="pr-3 font-weight-bold">R$ {{ number_format($assinatura->valor_cobrado, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted pl-3">Método</td>
                            <td class="pr-3">
                                @php
                                    $mlabel = match($assinatura->metodo_cobranca) {
                                        'boleto' => 'Boleto',
                                        'pix'    => 'PIX',
                                        'cartao' => 'Cartão de Crédito',
                                        default  => ucfirst($assinatura->metodo_cobranca),
                                    };
                                @endphp
                                {{ $mlabel }}
                                @if ($assinatura->renovacao_automatica)
                                    <span class="badge badge-success ml-1" style="font-size:.62rem;">Auto</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted pl-3">Início</td>
                            <td class="pr-3">{{ $assinatura->data_inicio->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted pl-3">Vencimento</td>
                            <td class="pr-3">{{ $assinatura->data_vencimento->format('d/m/Y') }}</td>
                        </tr>
                        @if ($assinatura->dia_vencimento)
                            <tr>
                                <td class="text-muted pl-3">Dia cobr.</td>
                                <td class="pr-3">Todo dia {{ $assinatura->dia_vencimento }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted pl-3">Criado por</td>
                            <td class="pr-3">{{ $assinatura->criadoPor?->nome ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Alterar status --}}
            <div class="card mb-3" style="border-left:4px solid #6c757d;">
                <div class="card-header py-2">
                    <h5 class="card-title mb-0" style="font-size:.9rem;">
                        <i class="fas fa-toggle-on mr-1 text-secondary"></i> Status da Assinatura
                    </h5>
                </div>
                <div class="card-body py-2">
                    <form action="{{ route('assinatura.status', $assinatura) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="input-group input-group-sm">
                            <select name="status" class="form-control">
                                @foreach ([
                                    'ativo'               => 'Ativo',
                                    'trial'               => 'Trial / Avaliação',
                                    'aguardando_pagamento'=> 'Aguardando Pagamento',
                                    'inadimplente'        => 'Inadimplente',
                                    'cancelado'           => 'Cancelado',
                                ] as $val => $label)
                                    <option value="{{ $val }}" {{ $assinatura->status === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-secondary btn-sm">Salvar</button>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size:.73rem;">
                            Altere manualmente o status independente do pagamento.
                        </small>
                    </form>
                </div>
            </div>

            {{-- Itens do plano --}}
            <div class="card mb-3">
                <div class="card-header py-2">
                    <h5 class="card-title mb-0" style="font-size:.9rem;">
                        <i class="fas fa-list-check mr-1 text-success"></i> Itens do Plano
                    </h5>
                </div>
                <div class="card-body py-2">
                    <ul class="list-unstyled mb-0">
                        @foreach ($assinatura->plano->itens as $item)
                            <li class="mb-1" style="font-size:.8rem;">
                                <i class="fas fa-{{ $item->incluso ? 'check text-success' : 'times text-muted' }} mr-1" style="font-size:.7rem;"></i>
                                {{ $item->descricao }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>

        {{-- ── COLUNA DIREITA: pagamentos ──────────────────────────────────── --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-receipt mr-1 text-secondary"></i> Pagamentos
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm"
                            data-toggle="modal" data-target="#modalPagamento">
                        <i class="fas fa-plus mr-1"></i> Registrar Pagamento
                    </button>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:.83rem;">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-3">Vencimento</th>
                                <th>Método</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">Status</th>
                                <th>Pago em</th>
                                <th>Ref.</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assinatura->pagamentos as $pag)
                                <tr>
                                    <td class="pl-3 align-middle">{{ $pag->data_vencimento->format('d/m/Y') }}</td>
                                    <td class="align-middle">{{ $pag->metodo_texto }}</td>
                                    <td class="align-middle text-right font-weight-bold">
                                        R$ {{ number_format($pag->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge {{ $pag->status_badge['class'] }}">{{ $pag->status_badge['label'] }}</span>
                                    </td>
                                    <td class="align-middle text-muted">
                                        {{ $pag->pago_em ? $pag->pago_em->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="align-middle text-muted" style="font-size:.75rem;">
                                        {{ $pag->referencia_interna ?? '—' }}
                                    </td>
                                    <td class="text-center align-middle" style="white-space:nowrap;">
                                        @if ($pag->status !== 'pago')
                                            <button type="button" class="btn btn-xs btn-success mr-1"
                                                    onclick="abrirConfirmar({{ $pag->id }}, '{{ $pag->metodo }}', 'R$ {{ number_format($pag->valor, 2, ',', '.') }}')"
                                                    title="Confirmar pagamento">
                                                <i class="fas fa-check"></i> Confirmar
                                            </button>
                                        @endif
                                        @if ($pag->boleto_url)
                                            <a href="{{ $pag->boleto_url }}" target="_blank" rel="noopener"
                                               class="btn btn-xs btn-outline-secondary" title="Ver boleto">
                                                <i class="fas fa-barcode"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                                        Nenhum pagamento registrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@stop

@section('footer')

{{-- ══ MODAL: Registrar Pagamento (superadmin — sem restrições) ══ --}}
<div class="modal fade" id="modalPagamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('assinatura.pagamento.registrar', $assinatura) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#0a1628;">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-money-bill mr-1"></i> Registrar Pagamento
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" style="font-size:.83rem;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Área administrativa — sem restrições. Defina livremente o método, status e valor.
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Valor (R$) <span class="text-danger">*</span></label>
                                <input type="number" name="valor" step="0.01" min="0.01"
                                       class="form-control" value="{{ $assinatura->valor_cobrado }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Método <span class="text-danger">*</span></label>
                                <select name="metodo" class="form-control" required>
                                    <option value="boleto">Boleto Bancário</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao">Cartão de Crédito</option>
                                    <option value="dinheiro">Dinheiro em Espécie</option>
                                    <option value="transferencia">Transferência Bancária</option>
                                    <option value="manual">Manual / Outro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="pago">Pago ✔</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="vencido">Vencido</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Data Vencimento <span class="text-danger">*</span></label>
                                <input type="date" name="data_vencimento" class="form-control"
                                       value="{{ now()->addMonth()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Pago em</label>
                                <input type="date" name="pago_em" class="form-control"
                                       value="{{ now()->toDateString() }}">
                                <small class="text-muted">Deixe em branco se ainda não pago.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">ID Gateway / Comprovante</label>
                                <input type="text" name="gateway_payment_id" class="form-control"
                                       placeholder="Ex.: pi_3Nxxxxxxx, nº comprovante…">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Observação</label>
                                <input type="text" name="observacao" class="form-control"
                                       placeholder="Ex.: Pago em dinheiro na sede, recibo nº 123…" maxlength="500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══ MODAL: Confirmar Pagamento (superadmin — sem restrições) ══ --}}
<div class="modal fade" id="modalConfirmar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formConfirmar" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header" style="background:#166534;">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-circle mr-1"></i> Confirmar Recebimento
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success py-2 mb-3" style="font-size:.83rem;">
                        <i class="fas fa-check-circle mr-1"></i>
                        Confirmando pagamento de <strong id="confirmarValor"></strong>.
                        A assinatura será marcada como <strong>Ativa</strong>.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Data do Pagamento <span class="text-danger">*</span></label>
                                <input type="date" name="pago_em" class="form-control"
                                       value="{{ now()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Método Confirmado <span class="text-danger">*</span></label>
                                <select name="metodo" class="form-control" id="confirmarMetodo" required>
                                    <option value="boleto">Boleto Bancário</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao">Cartão de Crédito</option>
                                    <option value="dinheiro">Dinheiro em Espécie</option>
                                    <option value="transferencia">Transferência Bancária</option>
                                    <option value="manual">Manual / Outro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">ID Gateway / Comprovante</label>
                                <input type="text" name="gateway_payment_id" class="form-control"
                                       placeholder="Stripe ID, nº comprovante PIX, nº recibo…">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function abrirConfirmar(pagamentoId, metodo, valor) {
    document.getElementById('confirmarValor').textContent = valor;
    document.getElementById('formConfirmar').action =
        '/admin/assinatura/pagamento/' + pagamentoId + '/confirmar';
    const sel = document.getElementById('confirmarMetodo');
    if (sel) {
        // Tenta pré-selecionar o método atual
        for (let opt of sel.options) { if (opt.value === metodo) { opt.selected = true; break; } }
    }
    $('#modalConfirmar').modal('show');
}
</script>
@endpush

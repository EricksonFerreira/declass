@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card card-outline card-warning">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="fas fa-car-crash mr-2"></i>{{ $titulo }}
            </h3>
            <a href="{{ route('reserva.show', $reserva->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @php
                $calcao = $reserva->calcao ?? null;
                $podeCapturarCalcao = $calcao && method_exists($calcao, 'podeCapturar') && $calcao->podeCapturar();
                $valorSug = (float) ($valorSugerido ?? 0);
            @endphp

            <div class="row">
                <div class="col-lg-7">
                    <div class="mb-3">
                        <h5 class="mb-2"><i class="fas fa-info-circle mr-1"></i>Resumo</h5>
                        <div class="small text-muted">
                            Reserva <strong>#{{ $reserva->id }}</strong> &bull;
                            Cliente: <strong>{{ $reserva->usuario->nome ?? '-' }}</strong>
                            @if(!empty($reserva->usuario->numero_identificacao))
                                ({{ $reserva->usuario->numero_identificacao }})
                            @endif
                            <br>
                            Veículo: <strong>{{ $reserva->veiculo->nome ?? '-' }}</strong>
                            @if(!empty($reserva->veiculo->placa_licenca))
                                ({{ $reserva->veiculo->placa_licenca }})
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('reserva.avarias.cobrar', $reserva->codigoReserva->codigo) }}" id="formCobrancaAvaria">
                        @csrf

                        <div class="mb-3">
                            <h5 class="mb-2"><i class="fas fa-list mr-1"></i>Avarias desta reserva</h5>
                            @if($danos->isEmpty())
                                <div class="alert alert-info mb-0">
                                    Nenhuma avaria registrada para esta reserva. Você ainda pode registrar uma cobrança manual, se necessário.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width:36px"></th>
                                                <th>Local</th>
                                                <th>Descrição</th>
                                                <th class="text-right">Custo estimado</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($danos as $d)
                                                @php
                                                    $custo = (float) ($d->custo_estimado ?? 0);
                                                    $corrigido = !empty($d->correcao_data);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="checkbox"
                                                               class="js-dano-check"
                                                               name="dano_veiculo_ids[]"
                                                               value="{{ $d->id }}"
                                                               data-custo="{{ number_format($custo, 2, '.', '') }}"
                                                               {{ $corrigido ? '' : 'checked' }}>
                                                    </td>
                                                    <td>{{ strtoupper($d->local_dano ?? $d->localizacao_problema ?? '-') }}</td>
                                                    <td>{{ \Illuminate\Support\Str::limit($d->observacoes ?? $d->tipo_dano ?? '', 80) }}</td>
                                                    <td class="text-right">R$ {{ number_format($custo, 2, ',', '.') }}</td>
                                                    <td>
                                                        @if($corrigido)
                                                            <span class="badge badge-success">Corrigido</span>
                                                        @else
                                                            <span class="badge badge-warning">Pendente</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Valor (R$) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" class="form-control"
                                           id="valor" name="valor"
                                           value="{{ old('valor', $valorSug > 0 ? number_format($valorSug, 2, '.', '') : '') }}"
                                           required>
                                    <small class="text-muted">Sugestão: soma dos custos estimados dos danos selecionados.</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Descrição / justificativa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="descricao" id="descricao" required
                                           minlength="5" maxlength="2000"
                                           value="{{ old('descricao', 'Cobrança por avarias identificadas após o check-out da reserva #' . $reserva->id) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Método</label>
                            <select class="form-control" name="metodo" id="metodo">
                                @if($podeCapturarCalcao)
                                    <option value="calcao_capture">Capturar do calção (recomendado)</option>
                                @endif
                                <option value="stripe_charge">Cobrar no cartão (Stripe, off-session)</option>
                                <option value="manual">Manual (registrar pendente)</option>
                            </select>
                            @if($podeCapturarCalcao)
                                <small class="text-muted">Calção autorizado: R$ {{ number_format((float) $calcao->valor_autorizado, 2, ',', '.') }}</small>
                            @endif
                        </div>

                        <div class="alert alert-warning">
                            <strong>Atenção:</strong> cobranças off-session podem falhar se o cartão exigir autenticação (3DS).
                            Nesse caso, registre como manual e cobre diretamente com o cliente.
                        </div>

                        <button type="submit" class="btn btn-warning" id="btnCobrar">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Debitar valor
                        </button>
                    </form>
                </div>

                <div class="col-lg-5">
                    <div class="mb-3">
                        <h5 class="mb-2"><i class="fas fa-history mr-1"></i>Histórico recente</h5>
                        @if(empty($cobrancas) || (is_countable($cobrancas) && count($cobrancas) === 0))
                            <div class="text-muted small">Nenhuma cobrança registrada ainda.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Status</th>
                                            <th>Método</th>
                                            <th class="text-right">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cobrancas as $c)
                                            @php
                                                $badge = match($c->status) {
                                                    'cobrado' => 'success',
                                                    'falhou' => 'danger',
                                                    'pendente' => 'secondary',
                                                    default => 'warning',
                                                };
                                            @endphp
                                            <tr>
                                                <td>#{{ $c->id }}</td>
                                                <td><span class="badge badge-{{ $badge }}">{{ $c->status }}</span></td>
                                                <td>{{ $c->metodo }}</td>
                                                <td class="text-right">{{ $c->moeda }} {{ number_format((float) $c->valor, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    @if($calcao)
                        <div class="callout callout-info">
                            <h6 class="mb-1"><i class="fas fa-shield-alt mr-1"></i>Calção</h6>
                            <div class="small mb-1">Status: <strong>{{ $calcao->label_status ?? $calcao->status }}</strong></div>
                            <div class="small">Autorizado: <strong>R$ {{ number_format((float) $calcao->valor_autorizado, 2, ',', '.') }}</strong></div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@push('js')
<script>
(function () {
    const checks = Array.from(document.querySelectorAll('.js-dano-check'));
    const valorInput = document.getElementById('valor');
    const form = document.getElementById('formCobrancaAvaria');
    const btn = document.getElementById('btnCobrar');

    function sumSelected() {
        return checks.reduce((sum, el) => {
            if (!el.checked) return sum;
            const v = parseFloat(el.dataset.custo || '0') || 0;
            return sum + v;
        }, 0);
    }

    function updateValor() {
        if (!valorInput) return;
        const current = parseFloat(valorInput.value || '0') || 0;
        // Só auto-preenche quando vazio ou igual ao último auto-cálculo (heurística simples)
        if (!valorInput.dataset.touched || current === (parseFloat(valorInput.dataset.auto || '0') || 0)) {
            const s = sumSelected();
            valorInput.value = s > 0 ? s.toFixed(2) : valorInput.value;
            valorInput.dataset.auto = String(s);
        }
    }

    checks.forEach(el => el.addEventListener('change', updateValor));
    valorInput?.addEventListener('input', () => { valorInput.dataset.touched = '1'; });
    updateValor();

    form?.addEventListener('submit', function () {
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processando...';
        }
    });
})();
</script>
@endpush


@extends('adminlte::page')

@section('title', 'Monitoramento de Avarias')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? [['label' => 'Avarias'], ['label' => 'Monitoramento']]" />
    <x-page-title :title="$titulo ?? 'Monitoramento de Avarias'" />
@stop

@section('content')
    <div class="fc-page">
        <!-- Filters & Toolbar -->
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" class="form-control border-left-0" id="danoSearch" placeholder="Filtrar por veículo, placa, código de reserva, tipo ou local...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary border-left-0 bg-white" type="button" id="danoSearchClear" title="Limpar filtro">&times;</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 d-flex justify-content-md-end justify-content-start gap-2">
                        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'pendentes']) }}"
                            class="btn {{ ($filtro ?? 'pendentes') === 'pendentes' ? 'btn-primary' : 'btn-outline-secondary' }} font-weight-bold" style="border-radius: 8px;">
                            <i class="fas fa-exclamation-circle mr-1"></i> Pendentes
                        </a>
                        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'corrigidos']) }}"
                            class="btn {{ ($filtro ?? '') === 'corrigidos' ? 'btn-primary' : 'btn-outline-secondary' }} font-weight-bold" style="border-radius: 8px;">
                            <i class="fas fa-check-circle mr-1"></i> Corrigidos
                        </a>
                        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'todos']) }}"
                            class="btn {{ ($filtro ?? '') === 'todos' ? 'btn-primary' : 'btn-outline-secondary' }} font-weight-bold" style="border-radius: 8px;">
                            <i class="fas fa-list mr-1"></i> Todos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head d-flex justify-content-between align-items-center">
                    <h3 class="fc-title m-0">
                        <i class="fas fa-car-crash mr-2 text-primary"></i>
                        <span>Avarias e Danos Recentes <span class="fc-subtitle">Histórico de danos de check-in, check-out e manutenções</span></span>
                    </h3>
                    <span class="badge bg-light text-secondary border px-3 py-2 font-weight-bold" style="font-size: 0.9rem; border-radius: 8px;">
                        {{ $danos->total() }} Registro(s)
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover fc-table mb-0" style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-center">ID</th>
                                <th class="py-3">Veículo</th>
                                <th class="py-3">Reserva</th>
                                <th class="py-3">Local Avaria</th>
                                <th class="py-3">Tipo</th>
                                <th class="py-3">Observações / Laudo</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-center px-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($danos as $dano)
                                @php
                                    $codigoReservaDano = $dano->reservaCodigo?->codigo
                                        ?? $dano->reserva?->codigoReserva?->codigo
                                        ?? $dano->contratoCheckin?->reserva?->codigoReserva?->codigo;

                                    $danoSearch = \Illuminate\Support\Str::lower(trim(implode(' ', array_filter([
                                        (string) ($dano->id ?? ''),
                                        (string) ($dano->veiculo_id ?? ''),
                                        (string) ($dano->reserva_id ?? ''),
                                        (string) ($codigoReservaDano ?? ''),
                                        (string) ($dano->local_dano ?? ''),
                                        (string) ($dano->tipo_dano ?? ''),
                                        (string) ($dano->localizacao_problema ?? ''),
                                        (string) ($dano->observacoes ?? ''),
                                        (string) ($dano->correcao_descricao ?? ''),
                                        (string) (optional($dano->veiculo)->nome ?? ''),
                                        (string) (optional($dano->veiculo)->placa_licenca ?? ''),
                                    ]))));
                                @endphp
                                <tr data-search="{{ $danoSearch }}" class="bg-white">
                                    <td class="px-4 py-3 text-center font-weight-bold text-secondary">
                                        #{{ $dano->id }}
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ route('veiculo.show', $dano->veiculo_id) }}" class="font-weight-bold text-primary text-decoration-none">
                                            <i class="fas fa-car mr-1"></i>{{ optional($dano->veiculo)->nome ?? ('Veículo #' . $dano->veiculo_id) }}
                                        </a>
                                        <div class="text-xs text-muted mt-1 font-weight-medium">Placa: {{ optional($dano->veiculo)->placa_licenca ?? '-' }}</div>
                                    </td>
                                    <td class="py-3">
                                        @if($codigoReservaDano)
                                            <a href="{{ route('reserva.show', $codigoReservaDano) }}" class="badge badge-light text-secondary border px-2 py-1" style="border-radius: 6px;">
                                                <i class="fas fa-calendar-alt mr-1"></i>#{{ $codigoReservaDano }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-weight-semibold text-dark">{{ $dano->local_dano ? strtoupper((string) $dano->local_dano) : '-' }}</td>
                                    <td class="py-3 text-secondary">{{ $dano->tipo_dano ?? '-' }}</td>
                                    <td class="py-3" style="max-width: 280px;">
                                        @php
                                            $obs = trim((string) ($dano->observacoes ?? ''));
                                            $correcao = trim((string) ($dano->correcao_descricao ?? ''));
                                            $localizacao = trim((string) ($dano->localizacao_problema ?? ''));
                                        @endphp
                                        <div>
                                            @if($obs !== '')
                                                <div class="text-dark small"><strong>Inicial:</strong> {{ \Illuminate\Support\Str::limit($obs, 120) }}</div>
                                            @endif
                                            @if($correcao !== '')
                                                <div class="text-success small mt-1">
                                                    <i class="fas fa-check-circle mr-1"></i><strong>Laudo/Correção:</strong> {{ \Illuminate\Support\Str::limit($correcao, 120) }}
                                                </div>
                                            @endif
                                            @if($localizacao !== '')
                                                <div class="text-muted text-xs mt-1"><strong>Localização:</strong> {{ $localizacao }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        @php
                                            $badgeClass = match($dano->status) {
                                                'em_correcao' => 'fc-badge--receita',
                                                'corrigida', 'parcialmente_corrigida' => 'fc-badge--pago',
                                                'descartada' => 'fc-badge--cancelado',
                                                default => 'fc-badge--despesa'
                                            };
                                            $badgeText = match($dano->status) {
                                                'em_correcao' => 'Em Correção',
                                                'corrigida' => 'Corrigido',
                                                'parcialmente_corrigida' => 'Parcialmente Corrigido',
                                                'descartada' => 'Descartado',
                                                default => 'Pendente'
                                            };
                                        @endphp
                                        <span class="fc-badge {{ $badgeClass }}">
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center px-4">
                                        @if($dano->status === 'pendente')
                                            <div class="dano-actions d-flex flex-column gap-2 align-items-center">
                                                <button type="button"
                                                    class="btn btn-xs btn-primary btn-resolver-avaria font-weight-bold w-100"
                                                    data-id="{{ $dano->id }}"
                                                    data-veiculo="{{ optional($dano->veiculo)->placa_licenca ?? $dano->veiculo_id }}"
                                                    data-create-os-url="{{ route('ordem-servico.create', ['veiculo_id' => $dano->veiculo_id, 'dano_ids' => [$dano->id]]) }}"
                                                    style="border-radius: 6px;">
                                                    <i class="fas fa-wrench mr-1"></i> Resolver Avaria
                                                </button>
                                            </div>
                                        @elseif($dano->status === 'em_correcao')
                                            <div class="dano-actions d-flex flex-column gap-1 align-items-center">
                                                @if($dano->os_associada)
                                                    <a href="{{ route('ordem-servico.show', $dano->os_associada) }}" class="btn btn-xs btn-outline-primary font-weight-bold w-100" style="border-radius: 6px;">
                                                        <i class="fas fa-tools mr-1"></i> Ver OS
                                                    </a>
                                                    <button type="button" class="btn btn-xs btn-outline-danger btn-remover-da-os font-weight-bold w-100 mt-1" 
                                                        data-id="{{ $dano->id }}" style="border-radius: 6px;">
                                                        <i class="fas fa-unlink mr-1"></i> Remover da OS
                                                    </button>
                                                @else
                                                    <span class="text-muted text-xs">Em Correção</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="dano-actions d-flex flex-column gap-1 align-items-center">
                                                @if($dano->os_associada)
                                                    <a href="{{ route('ordem-servico.show', $dano->os_associada) }}" class="btn btn-xs btn-outline-secondary font-weight-bold w-100" style="border-radius: 6px;">
                                                        <i class="fas fa-eye mr-1"></i> Ver OS Resolução
                                                    </a>
                                                @else
                                                    <span class="text-muted text-xs"><i class="fas fa-check-circle text-success mr-1"></i> Resolvido (Rápido)</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">Nenhuma avaria encontrada para o filtro selecionado.</td>
                                </tr>
                            @endforelse
                            <tr id="danoNoResultsRow" class="d-none">
                                <td colspan="8" class="text-center py-5 text-muted">Sem resultados para o filtro.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @if($danos->hasPages())
                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
                        {{ $danos->appends(['filtro' => $filtro ?? 'pendentes'])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- modal -->
    <div class="modal fade" id="modalCorrecao" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0 bg-light py-3">
                    <h5 class="modal-title font-weight-bold text-dark">Registrar Correção Rápida</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Veículo: <strong id="modalVeiculoNome" class="text-dark">-</strong></p>
                    <div class="form-group mb-0">
                        <label for="correcaoTexto" class="font-weight-semibold text-secondary small">Descrição da Correção Realizada</label>
                        <textarea id="correcaoTexto" class="form-control" rows="4" placeholder="Descreva o serviço de reparo ou troca realizado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 py-3 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                    <button type="button" class="btn btn-primary font-weight-bold px-4" id="btnSalvarCorrecao" style="border-radius: 8px;">Salvar Correção</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Escolha Resolução -->
    <div class="modal fade" id="modalResolucaoOpcao" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0 bg-light py-3">
                    <h5 class="modal-title font-weight-bold text-dark">Tratamento de Avaria</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-muted mb-4">Escolha a forma de tratamento para esta avaria:</p>
                    <div class="d-flex flex-column gap-3">
                        <button type="button" class="btn btn-outline-primary btn-lg font-weight-bold py-3 w-100 mb-2" id="btnOpcaoCorrecaoRapida" style="border-radius: 10px;">
                            <i class="fas fa-check-circle mr-2 text-success"></i> Correção Rápida Manual
                            <small class="d-block text-muted font-weight-normal mt-1 text-xs">Registrar solução imediata sem Ordem de Serviço</small>
                        </button>
                        <a href="#" class="btn btn-primary btn-lg font-weight-bold py-3 w-100" id="btnOpcaoCriarOS" style="border-radius: 10px;">
                            <i class="fas fa-tools mr-2"></i> Adicionar a uma nova OS
                            <small class="d-block text-white-50 font-weight-normal mt-1 text-xs">Encaminhar veículo para oficina mecânica/funilaria</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('financeiro.fluxo_caixa._styles')
@stop

@section('js')
<script>
(function () {
    'use strict';

    var danoIdAtual = null;
    var searchInput = document.getElementById('danoSearch');
    var searchClear = document.getElementById('danoSearchClear');
    var noResultsRow = document.getElementById('danoNoResultsRow');

    function applySearch() {
        if (!searchInput) return;
        var q = (searchInput.value || '').toLowerCase().trim();
        localStorage.setItem('danoMonitorSearch', q);

        var rows = document.querySelectorAll('tbody tr[data-search]');
        var matchCount = 0;
        rows.forEach(function (row) {
            var hay = (row.dataset.search || '');
            var ok = !q || hay.indexOf(q) !== -1;
            row.style.display = ok ? '' : 'none';
            if (ok) matchCount++;
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('d-none', matchCount !== 0 || !q);
        }
    }

    if (searchInput && searchClear) {
        var saved = localStorage.getItem('danoMonitorSearch') || '';
        if (saved) searchInput.value = saved;
        searchInput.addEventListener('input', applySearch);
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            applySearch();
            searchInput.focus();
        });
        applySearch();
    }

    document.querySelectorAll('.btn-resolver-avaria').forEach(function (btn) {
        btn.addEventListener('click', function () {
            danoIdAtual = this.dataset.id;
            var veiculoPlaca = this.dataset.veiculo || '-';
            var createOsUrl = this.dataset.createOsUrl || '#';

            document.getElementById('btnOpcaoCriarOS').setAttribute('href', createOsUrl);
            document.getElementById('modalVeiculoNome').textContent = veiculoPlaca;
            document.getElementById('correcaoTexto').value = '';

            $('#modalResolucaoOpcao').modal('show');
        });
    });

    document.getElementById('btnOpcaoCorrecaoRapida').addEventListener('click', function() {
        $('#modalResolucaoOpcao').modal('hide');
        setTimeout(function() {
            $('#modalCorrecao').modal('show');
        }, 300);
    });

    document.querySelectorAll('.btn-remover-da-os').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var danoId = this.dataset.id;
            if (!confirm('Confirma a remoção desta avaria da Ordem de Serviço? Ela voltará para o status Pendente.')) {
                return;
            }

            var url = '/admin/ordem-servico/remover-dano/' + danoId;
            var token = document.querySelector('meta[name="csrf-token"]');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                    'Accept': 'application/json'
                }
            })
            .then(function (res) {
                if (res.ok) {
                    location.reload();
                } else {
                    alert('Erro ao remover avaria da OS.');
                }
            })
            .catch(function () {
                location.reload();
            });
        });
    });

    document.getElementById('btnSalvarCorrecao').addEventListener('click', function () {
        var correcao = document.getElementById('correcaoTexto').value.trim();
        if (!correcao) {
            alert('Por favor, descreva a correcao realizada.');
            return;
        }

        var baseUrl = @json(route('veiculo-dano.correcao', ['id' => '__DANO_ID__']));
        var url = baseUrl.replace('__DANO_ID__', danoIdAtual);
        var token = document.querySelector('meta[name="csrf-token"]');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ correcao_descricao: correcao })
        })
        .then(function (res) {
            if (res.ok) {
                $('#modalCorrecao').modal('hide');
                location.reload();
            } else {
                return res.json().then(function (data) {
                    alert(data.message || 'Erro ao salvar correcao.');
                });
            }
        })
        .catch(function () {
            $('#modalCorrecao').modal('hide');
            location.reload();
        });
    });
}());
</script>
@stop

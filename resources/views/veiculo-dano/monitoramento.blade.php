@extends('adminlte::page')

@section('title', 'Monitoramento de Avarias')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? [['label' => 'Avarias'], ['label' => 'Monitoramento']]" />
    <x-page-title :title="$titulo ?? 'Monitoramento de Avarias'" />
@stop

@section('content')
    <div class="dano-monitor-page">
        <div class="card p-2 dano-monitor-card">
            <div class="card-header px-0 pt-0 pb-2 border-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap dano-monitor-toolbar">
                    <h3 class="card-title text-sm text-bold mb-2 mb-md-0">
                        REGISTROS <span class="badge badge-success">{{ $danos->total() }}</span>
                    </h3>
                    <div class="dano-toolbar-right">
                        <div class="input-group input-group-sm dano-search">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" id="danoSearch" placeholder="Filtrar: veiculo, placa, reserva, tipo, local...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="danoSearchClear" title="Limpar filtro">&times;</button>
                            </div>
                        </div>

                        <div class="dano-filter-tabs">
                            <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'pendentes']) }}"
                                class="btn btn-xs {{ ($filtro ?? 'pendentes') === 'pendentes' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-exclamation-circle mr-1"></i>Pendentes
                            </a>
                            <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'corrigidos']) }}"
                                class="btn btn-xs {{ ($filtro ?? '') === 'corrigidos' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-check-circle mr-1"></i>Corrigidos
                            </a>
                            <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'todos']) }}"
                                class="btn btn-xs {{ ($filtro ?? '') === 'todos' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-list mr-1"></i>Todos
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-1 table-responsive">
                <table class="table table-hover table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Veiculo</th>
                            <th>Reserva</th>
                            <th>Local</th>
                            <th>Tipo</th>
                            <th>Observacoes</th>
                            <th>Status</th>
                            <th class="text-center">Acao</th>
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
                            <tr data-search="{{ $danoSearch }}">
                                <td class="text-center">{{ $dano->id }}</td>
                                <td>
                                    <a href="{{ route('veiculo.show', $dano->veiculo_id) }}" class="badge badge-info">
                                        <i class="fas fa-car mr-1"></i>{{ optional($dano->veiculo)->nome ?? ('Veiculo #' . $dano->veiculo_id) }}
                                    </a>
                                    <div class="mt-1">
                                        <span class="badge badge-light">
                                            {{ optional($dano->veiculo)->placa_licenca ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($codigoReservaDano)
                                        <a href="{{ route('reserva.show', $codigoReservaDano) }}" class="badge badge-secondary">
                                            <i class="fas fa-calendar-alt mr-1"></i>#{{ $codigoReservaDano }}
                                        </a>
                                    @else
                                        <span class="badge badge-light">-</span>
                                    @endif
                                </td>
                                <td>{{ $dano->local_dano ? strtoupper((string) $dano->local_dano) : '-' }}</td>
                                <td>{{ $dano->tipo_dano ?? '-' }}</td>
                                <td>
                                    @php
                                        $obs = trim((string) ($dano->observacoes ?? ''));
                                        $correcao = trim((string) ($dano->correcao_descricao ?? ''));
                                        $localizacao = trim((string) ($dano->localizacao_problema ?? ''));
                                        $collapseId = 'dano_desc_' . $dano->id;
                                        $summary = $obs !== '' ? $obs : ($correcao !== '' ? $correcao : '-');
                                        $hasDetails = ($obs !== '' && mb_strlen($obs) > 70)
                                            || $correcao !== ''
                                            || $localizacao !== '';
                                    @endphp

                                    <div class="dano-desc">
                                        <div class="dano-desc__summary">
                                            <span class="dano-desc__text">{{ \Illuminate\Support\Str::limit($summary, 70) }}</span>
                                            @if($hasDetails)
                                                <a class="dano-desc__toggle"
                                                   data-toggle="collapse"
                                                   href="#{{ $collapseId }}"
                                                   role="button"
                                                   aria-expanded="false"
                                                   aria-controls="{{ $collapseId }}">
                                                    Detalhes
                                                </a>
                                            @endif
                                        </div>

                                        @if($hasDetails)
                                            <div class="collapse mt-2" id="{{ $collapseId }}">
                                                <div class="dano-desc__box">
                                                    @if($localizacao !== '')
                                                        <div><span class="dano-desc__label">Localizacao:</span> {{ $localizacao }}</div>
                                                    @endif
                                                    @if($obs !== '')
                                                        <div class="mt-1"><span class="dano-desc__label">Observacoes:</span> {{ $obs }}</div>
                                                    @endif
                                                    @if($dano->correcao_data)
                                                        <div class="mt-1">
                                                            <span class="dano-desc__label">Correcao:</span>
                                                            {{ $correcao !== '' ? $correcao : '-' }}
                                                            <span class="text-muted">({{ $dano->correcao_data->format('d/m/Y H:i') }})</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($dano->correcao_data)
                                        <span class="badge badge-success">Concluido</span>
                                    @else
                                        <span class="badge badge-warning">Pendente</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!$dano->correcao_data)
                                        <div class="dano-actions">
                                            <button type="button"
                                                class="btn btn-xs btn-primary btn-registrar-correcao"
                                                data-id="{{ $dano->id }}"
                                                data-veiculo="{{ optional($dano->veiculo)->placa ?? $dano->veiculo_id }}"
                                                data-create-os-url="{{ empty($dano->os_aberta) ? route('ordem-servico.create', ['veiculo_id' => $dano->veiculo_id, 'dano_ids' => [$dano->id]]) : '' }}"
                                                data-os-url="{{ !empty($dano->os_aberta) ? route('ordem-servico.show', $dano->os_aberta) : '' }}">
                                                <i class="fas fa-wrench mr-1"></i>Registrar correcao
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Nenhuma avaria encontrada para o filtro selecionado.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="danoNoResultsRow" class="d-none">
                            <td colspan="8" class="text-center py-4 text-muted">
                                Sem resultados para o filtro.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($danos->hasPages())
                <div class="card-footer bg-white border-0 pt-2">
                    {{ $danos->appends(['filtro' => $filtro ?? 'pendentes'])->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="modalCorrecao" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content dano-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar correcao</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">Veiculo: <strong id="modalVeiculoNome">-</strong></p>
                    <label for="correcaoTexto">Descrição da correcao realizada</label>
                    <textarea id="correcaoTexto" class="form-control" rows="4" placeholder="Descreva a correcao realizada..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarCorrecao">Salvar correcao</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .dano-monitor-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .dano-monitor-toolbar {
        gap: 12px;
    }

    .dano-toolbar-right {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
        align-items: center;
    }

    .dano-search {
        width: 320px;
        max-width: 100%;
    }

    .dano-filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: flex-end;
    }

    .dano-obs-cell {
        max-width: 220px;
    }

    .dano-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: center;
    }

    .dano-modal {
        border-radius: 14px;
        overflow: hidden;
    }

    .dano-desc__summary {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
    }

    .dano-desc__text {
        color: #0f172a;
        min-width: 0;
    }

    .dano-desc__toggle {
        font-size: 12px;
        font-weight: 600;
        color: #2563eb;
        white-space: nowrap;
    }

    .dano-desc__box {
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 12px;
        color: #334155;
        line-height: 1.4;
    }

    .dano-desc__label {
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-size: 10px;
    }

    @media (max-width: 767.98px) {
        .dano-toolbar-right {
            width: 100%;
            justify-content: flex-start;
        }

        .dano-search {
            width: 100%;
        }

        .dano-filter-tabs {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
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

    document.querySelectorAll('.btn-registrar-correcao').forEach(function (btn) {
        var wrapper = btn.closest('.dano-actions');
        var osUrl = btn.dataset.osUrl || '';
        var createOsUrl = btn.dataset.createOsUrl || '';
        var targetUrl = osUrl || createOsUrl;

        if (wrapper && targetUrl) {
            var osLink = document.createElement('a');
            osLink.href = targetUrl;
            osLink.className = 'btn btn-xs btn-outline-secondary';
            osLink.innerHTML = osUrl
                ? '<i class="fas fa-clipboard-check mr-1"></i>Ver OS'
                : '<i class="fas fa-tools mr-1"></i>Abrir OS';
            wrapper.appendChild(osLink);
        }

        btn.addEventListener('click', function () {
            danoIdAtual = this.dataset.id;
            document.getElementById('modalVeiculoNome').textContent = this.dataset.veiculo || '-';
            document.getElementById('correcaoTexto').value = '';
            $('#modalCorrecao').modal('show');
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

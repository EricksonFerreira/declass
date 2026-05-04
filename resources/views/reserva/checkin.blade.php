@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

    <div class="col-12 px-0">
        <div class="ci-card">

            {{-- ══════════════════════════════════════
                 HEADER ESCURO
            ══════════════════════════════════════ --}}
            <div class="ci-card-header">
                <div class="ci-header-left">
                    <div class="ci-header-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="ci-header-eyebrow">Check-in</div>
                        <h4 class="ci-header-title">Reserva #{{ $reserva->id ?? '' }}</h4>
                        <p class="ci-header-sub">
                            <i class="fas fa-user mr-1"></i>{{ $reserva->usuario->nome ?? 'Cliente' }}
                            &nbsp;&middot;&nbsp;
                            <i class="fas fa-car mr-1"></i>{{ $reserva->modelos->nome ?? 'Veiculo' }}
                        </p>
                    </div>
                </div>
                <div class="ci-header-right">
                    @php
                        $dataInicio = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : null;
                        $dataFim    = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y')    : null;
                    @endphp
                    @if($dataInicio)
                        <div class="ci-date-badge">
                            <i class="fas fa-calendar-alt"></i>
                            <span>{{ $dataInicio }}@if($dataFim) &rarr; {{ $dataFim }}@endif</span>
                        </div>
                    @endif
                    <a href="{{ route('reserva.show', $reserva->id) }}" class="ci-btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 BODY
            ══════════════════════════════════════ --}}
            <div class="ci-body">

                <input type="hidden" id="reserva_id" name="reserva_id" value="{{ $reserva->id }}">

                {{-- Indicador de passos --}}
                <div class="step-indicator" role="tablist">
                    @php
                        $steps = [
                            ['label' => 'Dados do Veiculo', 'icon' => 'fas fa-car'],
                            ['label' => 'Fotos do Veiculo', 'icon' => 'fas fa-camera'],
                            ['label' => 'Estado do Veiculo', 'icon' => 'fas fa-search'],
                            ['label' => 'Observacoes', 'icon' => 'fas fa-comment-alt'],
                        ];
                    @endphp
                    @foreach($steps as $i => $step)
                        <button type="button" class="step-btn {{ $i === 0 ? 'active' : '' }}" data-step-target="{{ $i + 1 }}">
                            <span class="step-num">{{ $i + 1 }}</span>
                            <span class="step-label-text">
                                <i class="{{ $step['icon'] }}"></i>
                                {{ $step['label'] }}
                            </span>
                        </button>
                        @if($i < count($steps) - 1)
                            <div class="step-divider"></div>
                        @endif
                    @endforeach
                </div>

                {{-- ═══════════════════════════════════
                     PASSO 1 — Dados do Veiculo
                ═══════════════════════════════════ --}}
                <section class="wizard-step active" data-step="1">
                    <div class="rw-step-content-header">
                        <div class="rw-step-content-icon"><i class="fas fa-car"></i></div>
                        <div>
                            <h5>Dados do Veiculo</h5>
                            <p>Selecione o veiculo, registre a quilometragem atual e defina data e hora de saida.</p>
                        </div>
                    </div>

                    <form id="form_dados_veiculos">

                        @if($contratoExistente && $contratoExistente->assinado == 'S')
                        <div class="alert alert-danger mb-3" role="alert">
                            <i class="fas fa-lock mr-2"></i>
                            <strong>Contrato assinado.</strong> Os campos estão bloqueados para edição. Para modificar, acesse a reserva, exclua o contrato e crie um novo.
                        </div>
                        @endif

                        {{-- Banner de upgrade de categoria (exibido via JS quando categoria original indisponivel) --}}
                        <div id="upgrade-banner" class="alert alert-warning d-none mb-3" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-arrow-circle-up fa-2x mr-3 text-warning mt-1"></i>
                                <div>
                                    <strong>Upgrade por Indisponibilidade de Categoria</strong>
                                    <p class="mb-1 small">
                                        Nao ha veiculos disponiveis na categoria reservada
                                        (<span id="upgrade-grupo-original"></span>).
                                        Selecionamos abaixo os veiculos livres no periodo desta reserva.
                                        Somente os marcados como <strong>upgrade</strong> ficam habilitados.
                                    </p>
                                    <div class="small mb-2" style="opacity:.9">
                                        <span class="mr-2"><strong>Legenda:</strong></span>
                                        <span class="mr-3">⬆ elegivel (upgrade)</span>
                                        <span>⬇ inferior (bloqueado)</span>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small mb-0">Motivo do upgrade</label>
                                        <input type="text" id="motivo_upgrade_input" class="form-control form-control-sm"
                                               value="Indisponibilidade na categoria original" maxlength="500">
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="upgrade_mostrar_bloqueados">
                                        <label class="form-check-label small" for="upgrade_mostrar_bloqueados">
                                            Mostrar veiculos inferiores (bloqueados)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Selecao do veiculo --}}
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-car"></i>
                                <div>
                                    <strong>Identificacao do Veiculo</strong>
                                    <span>Clique na lupa para buscar os veículos disponíveis no período desta reserva na locadora.</span>
                                </div>
                            </div>
                            @php
                                $ciVeiculoId  = old('veiculo',         $contratoExistente?->veiculo_id ?? '');
                                $ciVeiculoTxt = old('veiculo_display',
                                    $contratoExistente?->veiculo
                                        ? ($contratoExistente->veiculo->nome . ($contratoExistente->veiculo->placa_licenca ? ' (' . $contratoExistente->veiculo->placa_licenca . ')' : ''))
                                        : ''
                                );
                            @endphp
                            <div class="row">
                                <div class="col-md-6" id="veiculo_col">
                                    <label for="veiculo_display">Veiculo <span class="req">*</span></label>
                                    <div class="input-group">
                                        <input type="hidden" id="veiculo" name="veiculo" value="{{ $ciVeiculoId }}">
                                        <input type="text" id="veiculo_display" class="form-control form-control-sm" readonly placeholder="Clique na lupa para selecionar" value="{{ $ciVeiculoTxt }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculoCheckin" title="Buscar veículo disponível"><i class="fas fa-search"></i></button>
                                            <button type="button" class="btn btn-outline-danger d-none" id="btnLimparVeiculoCheckin" title="Limpar veículo"><i class="fas fa-times"></i></button>
                                        </div>
                                        <button type="button" class="btn btn-outline-info btn-sm ml-2" id="btnDiagnostico" title="Ver diagnóstico de disponibilidade">
                                            <i class="fas fa-stethoscope"></i> Diagnóstico
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal de busca de veículos disponíveis no período da reserva --}}
                        <x-veiculo.busca-avancada-modal
                            prefix="checkin"
                            title="Veículos Disponíveis no Período"
                            buscar-url="{{ route('reserva.checkin.veiculosModal', $reserva->id) }}"
                            trigger-id="btnBuscarVeiculoCheckin"
                            hidden-input-id="veiculo"
                            display-input-id="veiculo_display"
                            clear-btn-id="btnLimparVeiculoCheckin"
                            auto-fill-kms-selector="#quilometragem"
                            auto-fill-nivel-selector="#combustivel"
                        />

                        {{-- Diagnóstico de disponibilidade --}}
                        <div class="modal fade" id="modalDiagnostico" tabindex="-1">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5><i class="fas fa-stethoscope mr-2"></i>Diagnóstico de Disponibilidade</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body" id="diagnosticoResultado">
                                        <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Analisando veículos...</p></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @push('js')
                            <script>
                                (function () {
                                    // Auto-search quando o modal abre
                                    var modalEl = document.getElementById('checkin_modalBuscarVeiculo');
                                    if (modalEl) {
                                        modalEl.addEventListener('shown.bs.modal', function () {
                                            var btnBuscar = document.getElementById('checkin_btnAplicarFiltroVeiculo');
                                            if (btnBuscar) btnBuscar.click();
                                        });
                                        // Bootstrap 3/AdminLTE usa jQuery events
                                        if (window.$) {
                                            window.$('#checkin_modalBuscarVeiculo').on('shown.bs.modal', function () {
                                                var btnBuscar = document.getElementById('checkin_btnAplicarFiltroVeiculo');
                                                if (btnBuscar) btnBuscar.click();
                                            });
                                        }
                                    }

                                    // Ao selecionar veículo: preenche kms (min = kms do veículo) e combustível
                                    document.addEventListener('veiculo:selecionado', function (ev) {
                                        var d = ev.detail || {};
                                        var kms = d.kms || '';
                                        var quil = document.getElementById('quilometragem');

                                        if (kms && quil) {
                                            var kmsNum = Number(kms);
                                            quil.min = String(kmsNum);
                                            quil.value = kmsNum;
                                        }

                                        var combEl = document.getElementById('combustivel');
                                        if (combEl && d.nivel) {
                                            var found = Array.from(combEl.options).find(function (o) {
                                                return String(o.value).toLowerCase() === String(d.nivel).toLowerCase()
                                                    || String(o.text).toLowerCase() === String(d.nivel).toLowerCase();
                                            });
                                            if (found) combEl.value = found.value;
                                        }

                                        var dataEl = document.getElementById('data_saida');
                                        var horaEl = document.getElementById('hora_saida');
                                        var now = new Date();
                                        if (dataEl && !dataEl.value) {
                                            dataEl.value = now.getFullYear() + '-'
                                                + String(now.getMonth() + 1).padStart(2, '0') + '-'
                                                + String(now.getDate()).padStart(2, '0');
                                        }
                                        if (horaEl && !horaEl.value) {
                                            horaEl.value = String(now.getHours()).padStart(2, '0') + ':'
                                                + String(now.getMinutes()).padStart(2, '0');
                                        }
                                    });

                                    // Impede que o usuário diminua o valor de quilometragem abaixo do mínimo
                                    var quilEl = document.getElementById('quilometragem');
                                    if (quilEl) {
                                        quilEl.addEventListener('input', function () {
                                            var min = Number(this.min);
                                            if (min && Number(this.value) < min) {
                                                this.value = min;
                                            }
                                        });
                                    }
                                 }());
                            </script>
                            <script>
                                document.getElementById('btnDiagnostico')?.addEventListener('click', function () {
                                    if (typeof renderDiagnostico === 'function') { renderDiagnostico(); }
                                    else {
                                        window.renderDiagnostico = function() {
                                            var modal = $('#modalDiagnostico');
                                            modal.modal('show');
                                            document.getElementById('diagnosticoResultado').innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><p class="mt-3 text-muted">Analisando veículos...</p></div>';
                                            fetch('{{ route('reserva.checkin.diagnostico', $reserva->id) }}', { headers: { 'Accept': 'application/json' } })
                                                .then(function (r) { return r.json(); })
                                                .then(function (data) {
                                                    var h = '';
                                                    if (!data.veiculos || !data.veiculos.length) {
                                                        h = '<div class="alert alert-info text-center py-4"><i class="fas fa-car fa-3x mb-2 d-block text-muted"></i>Nenhum veículo encontrado na estação da reserva.</div>';
                                                    } else {
                                                        var disp = data.veiculos.filter(function(v){return v.disponivel});
                                                        var ind = data.veiculos.filter(function(v){return !v.disponivel});
                                                        h += '<div class="d-flex gap-3 mb-3 flex-wrap">';
                                                        h += '<span class="badge badge-success p-2" style="font-size:.85rem;"><i class="fas fa-check-circle mr-1"></i>' + disp.length + ' disponível(is)</span>';
                                                        h += '<span class="badge badge-secondary p-2" style="font-size:.85rem;"><i class="fas fa-times-circle mr-1"></i>' + ind.length + ' indisponível(is)</span>';
                                                        h += '<span class="badge badge-info p-2" style="font-size:.85rem;"><i class="fas fa-car mr-1"></i>' + data.veiculos.length + ' total</span>';
                                                        if (data.reserva_info) {
                                                            h += '<a href="' + (data.reserva_info.url || '#') + '" target="_blank" class="badge badge-light p-2" style="font-size:.82rem;border:1px solid #dee2e6;"><i class="fas fa-external-link-alt mr-1"></i>Reserva #' + data.reserva_info.id + '</a>';
                                                        }
                                                        h += '</div>';

                                                        ind.forEach(function (v) {
                                                            var r = {};
                                                            v.regras.forEach(function(rr) { r[rr.nome] = rr; });
                                                            h += '<div class="card border mb-3"><div class="card-header py-2 d-flex justify-content-between align-items-center" style="background:#f8f9fa;">';
                                                            h += '<span><strong>' + v.nome + '</strong> <small class="text-muted">' + v.placa + ' · ' + v.modelo + ' · ' + v.cor + '</small></span>';
                                                            h += '<span class="badge badge-secondary">Indisponível</span></div><div class="card-body py-2">';

                                                            var ordem = ['Estação','Disponibilidade','Status','Reservas','Movimentações'];
                                                            ordem.forEach(function (nk) {
                                                                var rr = r[nk];
                                                                if (!rr) return;
                                                                var ok = rr.ok, ic = ok ? 'fa-check-circle text-success' : 'fa-times-circle text-danger', bg = ok ? '#f0fdf4' : '#fef2f2';
                                                                h += '<div class="d-flex align-items-start gap-2 p-2 mb-1 rounded" style="background:' + bg + ';">';
                                                                h += '<i class="fas ' + ic + ' mt-1" style="font-size:1rem;"></i>';
                                                                h += '<div class="flex-grow-1"><strong style="font-size:.85rem;">' + nk + '</strong>';
                                                                h += '<div style="font-size:.8rem;color:#374151;">' + (rr.info || '') + '</div>';
                                                                if (!ok && rr.solucao) h += '<div style="font-size:.78rem;color:#dc2626;margin-top:2px;"><i class="fas fa-lightbulb mr-1"></i>' + rr.solucao + '</div>';
                                                                if (rr.itens && rr.itens.length) {
                                                                    rr.itens.forEach(function (item) {
                                                                        h += '<a href="' + item.url + '" target="_blank" class="btn btn-xs btn-outline-primary mt-1 mr-1" style="font-size:.75rem;"><i class="fas fa-external-link-alt mr-1"></i>' + item.label + '</a>';
                                                                    });
                                                                }
                                                                h += '</div></div>';
                                                            });
                                                            h += '</div></div>';
                                                        });

                                                        if (disp.length) {
                                                            h += '<h6 class="mt-4 mb-2"><i class="fas fa-check-circle text-success mr-1"></i>Veículos Disponíveis</h6>';
                                                            disp.forEach(function (v) {
                                                                h += '<div class="d-inline-block mr-2 mb-2 p-2 rounded border" style="background:#f0fdf4;"><strong style="font-size:.82rem;">' + v.nome + '</strong> <small class="text-muted">' + v.placa + '</small></div>';
                                                            });
                                                        }
                                                    }
                                                    document.getElementById('diagnosticoResultado').innerHTML = h;
                                                })
                                                .catch(function () { document.getElementById('diagnosticoResultado').innerHTML = '<div class="alert alert-danger text-center py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>Erro ao carregar diagnóstico.</div>'; });
                                        };
                                        renderDiagnostico();
                                    }
                                });
                            </script>
                        @endpush

                        {{-- Bloco: Condicao e saida --}}
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-tachometer-alt"></i>
                                <div>
                                    <strong>Condicao e Saida</strong>
                                    <span>Quilometragem atual, nivel de combustivel e data/hora de saida</span>
                                </div>
                            </div>
                            @php
                                $ciKm        = old('quilometragem', $contratoExistente?->quilometragem_atual ?? '');
                                $ciCombust   = old('combustivel',   $contratoExistente?->nivel_combustivel ?? '');
                                $ciDataSaida = old('data_saida',    $contratoExistente?->data_saida ?? '');
                                $ciHoraSaida = old('hora_saida',    $contratoExistente?->hora_saida ? substr($contratoExistente->hora_saida, 0, 5) : '');
                                $niveis = ['Reserva','1/8','1/4','3/8','1/2','5/8','3/4','7/8','Cheio'];
                            @endphp
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="quilometragem">Quilometragem Atual <span class="req">*</span></label>
                                    <input type="number" required class="form-control form-control-sm" id="quilometragem" name="quilometragem"
                                           placeholder="Ex: 45200"
                                           value="{{ $ciKm }}"
                                           min="{{ $ciKm ?: 0 }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="combustivel">Nivel de Combustivel <span class="req">*</span></label>
                                    <select class="form-control form-control-sm" id="combustivel" required name="combustivel">
                                        @foreach($niveis as $nivel)
                                            <option {{ $ciCombust === $nivel ? 'selected' : '' }}>{{ $nivel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="data_saida">Data de Saida <span class="req">*</span></label>
                                    <input type="date" class="form-control form-control-sm" required id="data_saida" name="data_saida"
                                           value="{{ $ciDataSaida }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="hora_saida">Hora de Saida <span class="req">*</span></label>
                                    <input type="time" class="form-control form-control-sm" required id="hora_saida" name="hora_saida"
                                           value="{{ $ciHoraSaida }}">
                                </div>
                            </div>
                        </div>

                    </form>
                </section>

                {{-- ═══════════════════════════════════
                     PASSO 2 — Estado do Veiculo
                ═══════════════════════════════════ --}}
                <section class="wizard-step" data-step="2">
                    <div class="rw-step-content-header">
                        <div class="rw-step-content-icon rw-step-content-icon--blue"><i class="fas fa-camera"></i></div>
                        <div>
                            <h5>Fotos do Veiculo</h5>
                            <p>Envie as fotos obrigatorias para registrar o estado do veiculo no check-in. Nao e possivel continuar sem concluir este passo.</p>
                        </div>
                    </div>

                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-images"></i>
                            <div>
                                <strong>Fotos Obrigatorias</strong>
                                <span>Direita, esquerda, frente, tras, velocimetro e combustivel</span>
                            </div>
                        </div>

                        @php
                            $fotos = [
                                ['id' => 'foto_direita',     'label' => 'Direita',              'col' => 'foto_direita_path'],
                                ['id' => 'foto_esquerda',    'label' => 'Esquerda',             'col' => 'foto_esquerda_path'],
                                ['id' => 'foto_frente',      'label' => 'Frente',               'col' => 'foto_frente_path'],
                                ['id' => 'foto_tras',        'label' => 'Tras',                 'col' => 'foto_tras_path'],
                                ['id' => 'foto_velocimetro', 'label' => 'Velocimetro',          'col' => 'foto_velocimetro_path'],
                                ['id' => 'foto_combustivel', 'label' => 'Medidor de Combustivel','col' => 'foto_combustivel_path'],
                            ];
                        @endphp

                        <div class="row">
                            @foreach($fotos as $f)
                                @php
                                    $fotoExistente = $contratoExistente?->{$f['col']} ?? null;
                                    $fotoUrl = $fotoExistente ? asset('storage/' . $fotoExistente) : null;
                                @endphp
                                <div class="col-md-4">
                                    <div class="ci-photo-card">
                                        <label for="{{ $f['id'] }}">
                                            {{ $f['label'] }}
                                            @if(!$contratoExistente)<span class="req">*</span>@endif
                                        </label>
                                        <input type="file" class="form-control form-control-sm" id="{{ $f['id'] }}" name="{{ $f['id'] }}" accept="image/*"
                                               @if(!$contratoExistente) required @endif>
                                        <div class="ci-photo-preview" data-preview-for="{{ $f['id'] }}">
                                            @if($fotoUrl)
                                                <img src="{{ $fotoUrl }}" alt="{{ $f['label'] }}" style="max-width:100%;max-height:160px;border-radius:6px;">
                                            @else
                                                <span class="ci-photo-preview__placeholder">
                                                    <i class="far fa-image mr-1"></i>Sem imagem
                                                </span>
                                            @endif
                                        </div>
                                        <small class="ci-hint">
                                            @if($fotoUrl)
                                                Foto atual exibida. Selecione um novo arquivo para substituir.
                                            @else
                                                Max 8MB. Formatos: JPG/PNG/WEBP.
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="wizard-step" data-step="3">
                    <div class="rw-step-content-header">
                        <div class="rw-step-content-icon rw-step-content-icon--amber"><i class="fas fa-search"></i></div>
                        <div>
                            <h5>Estado do Veiculo</h5>
                            <p>Clique em uma das vistas do veiculo para registrar um novo dano. O veiculo deve estar selecionado no Passo 1.</p>
                        </div>
                    </div>

                    {{-- Vistas do carro --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-car-crash"></i>
                            <div>
                                <strong>Vistas do Veiculo</strong>
                                <span>Clique na vista onde o dano se encontra para registra-lo</span>
                            </div>
                        </div>
                        <div class="car-views-grid">
                            @php
                                $vistas = [
                                    ['nome' => 'DIREITA',   'arquivo' => 'direita.jpg',   'icon' => 'fas fa-arrow-right'],
                                    ['nome' => 'ESQUERDA',  'arquivo' => 'esquerda.jpg',  'icon' => 'fas fa-arrow-left'],
                                    ['nome' => 'CIMA',      'arquivo' => 'cima.jpg',       'icon' => 'fas fa-arrow-up'],
                                    ['nome' => 'FRENTE',    'arquivo' => 'frente.jpg',     'icon' => 'fas fa-dot-circle'],
                                    ['nome' => 'ATRÁS',     'arquivo' => 'atras.jpg',      'icon' => 'fas fa-circle'],
                                ];
                            @endphp
                            @foreach($vistas as $vista)
                                <div class="car-view-card" onclick="setNomeDano('{{ $vista['nome'] }}')" data-toggle="modal" data-target="#detalhesDanoModal">
                                    <div class="car-view-card__img">
                                        <img src="{{ asset('assets/imgs/carro/' . $vista['arquivo']) }}" alt="{{ $vista['nome'] }}">
                                        <div class="car-view-card__overlay">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Registrar dano</span>
                                        </div>
                                    </div>
                                    <div class="car-view-card__label">
                                        <i class="{{ $vista['icon'] }}"></i>
                                        {{ $vista['nome'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tabelas de danos --}}
                    <div class="ci-damages-grid">
                        <div class="ci-block ci-block--no-mb">
                            <div class="ci-block-label">
                                <i class="fas fa-history"></i>
                                <div>
                                    <strong>Danos Existentes</strong>
                                    <span>Registros anteriores a esta reserva</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localizacao</th>
                                            <th>Descricao</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_existentes">
                                        <tr>
                                            <td colspan="3" class="damage-empty">
                                                <i class="fas fa-check-circle"></i>
                                                Selecione um veiculo para carregar os danos
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="ci-block ci-block--no-mb ci-block--amber">
                            <div class="ci-block-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Danos Detectados Nesta Reserva</strong>
                                    <span>Danos registrados durante o check-in atual</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localizacao</th>
                                            <th>Descricao</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_detectados">
                                        <tr>
                                            <td colspan="3" class="damage-empty">
                                                <i class="fas fa-shield-alt"></i>
                                                Nenhum dano detectado ainda
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('reserva.components.modal_dano_checkin')
                </section>

                {{-- ═══════════════════════════════════
                     PASSO 3 — Observacoes
                ═══════════════════════════════════ --}}
                <section class="wizard-step" data-step="4">
                    <div class="rw-step-content-header">
                        <div class="rw-step-content-icon rw-step-content-icon--green"><i class="fas fa-comment-alt"></i></div>
                        <div>
                            <h5>Observacoes e Notas</h5>
                            <p>Registre quaisquer observacoes adicionais sobre a saida do veiculo.</p>
                        </div>
                    </div>

                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-pen-alt"></i>
                            <div>
                                <strong>Notas e Observacoes na Saida</strong>
                                <span>Informacoes que ficam registradas no contrato</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="notas_saida">Notas na Saida</label>
                                <textarea class="form-control form-control-sm" id="notas_saida" name="notas_saida" rows="5" placeholder="Ex: Cliente informou arranhao no para-choque traseiro...">{{ old('notas_saida', $contratoExistente?->notas_saida ?? '') }}</textarea>
                                <small class="ci-hint">Notas visíveis no contrato gerado.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="observacoes_saida">Observacoes na Saida</label>
                                <textarea class="form-control form-control-sm" id="observacoes_saida" name="observacoes_saida" rows="5" placeholder="Ex: Veiculo entregue com tanque cheio...">{{ old('observacoes_saida', $contratoExistente?->observacoes_saida ?? '') }}</textarea>
                                <small class="ci-hint">Observacoes internas do operador.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Resumo antes de criar/salvar --}}
                    @if($contratoExistente && $contratoExistente->assinado == 'S')
                    {{-- Contrato assinado: bloqueia edição --}}
                    <div class="ci-summary-card" style="background:linear-gradient(135deg,#fff5f5 0%,#fee2e2 100%);border-color:#fca5a5;">
                        <div class="ci-summary-card__icon" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Contrato Assinado — Edição Bloqueada</strong>
                            <p>Este contrato já foi assinado em <em>{{ \Carbon\Carbon::parse($contratoExistente->data_assinatura)->format('d/m/Y H:i') }}</em> e não pode ser alterado. Para criar um novo contrato, acesse a página da reserva, exclua o contrato atual e recrie.</p>
                        </div>
                        <a href="{{ route('reserva.show', $reserva->id) }}" class="ci-btn-create" style="background:linear-gradient(135deg,#ef4444,#b91c1c);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            <i class="fas fa-arrow-left"></i>
                            Voltar para a Reserva
                        </a>
                    </div>
                    @else
                    <div class="ci-summary-card">
                        <div class="ci-summary-card__icon">
                            <i class="fas fa-{{ $contratoExistente ? 'edit' : 'rocket' }}"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            @if($contratoExistente)
                                <strong>Salvar alterações no contrato?</strong>
                                <p>Verifique se todos os dados estão corretos. Ao clicar em <em>Salvar Contrato</em> as alterações serão registradas e você será redirecionado para a reserva.</p>
                            @else
                                <strong>Pronto para criar o contrato?</strong>
                                <p>Verifique se todos os dados dos Passos 1 e 2 estao preenchidos. Ao clicar em <em>Criar Contrato</em> o contrato sera gerado e voce sera redirecionado para a reserva.</p>
                            @endif
                        </div>
                        <button type="button" class="ci-btn-create" id="btn_criar_contrato">
                            <i class="fas fa-{{ $contratoExistente ? 'save' : 'file-contract' }}"></i>
                            {{ $contratoExistente ? 'Salvar Contrato' : 'Criar Contrato' }}
                        </button>
                    </div>
                    @endif
                </section>

                {{-- Acoes do wizard --}}
                <div class="wizard-actions">
                    <button type="button" class="btn-action btn-action-secondary" id="wizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </button>
                    <div class="wizard-actions-right">
                        <button type="button" class="btn-action btn-primary" id="wizardNext">
                            Continuar<i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        {{-- @if(!($contratoExistente && $contratoExistente->assinado == 'S'))
                        <button type="button" class="btn-action btn-action-success d-none" id="wizardCreateBtn">
                            <i class="fas fa-{{ $contratoExistente ? 'save' : 'file-contract' }} mr-1"></i>
                            {{ $contratoExistente ? 'Salvar Contrato' : 'Criar Contrato' }}
                        </button>
                        @endif --}}
                    </div>
                </div>

            </div>{{-- /ci-body --}}
        </div>{{-- /ci-card --}}
    </div>

@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<style>
    @include('reserva.partials.vistoria_reserva_estilos_inner')

    /* ── Step content header (padrão add-edit) ── */
    .rw-step-content-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        background: #f8faff;
        border: 1px solid var(--c-border);
        border-left: 4px solid var(--c-indigo);
        border-radius: 12px;
        margin-bottom: 1rem;
    }
    .rw-step-content-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: color-mix(in srgb, var(--c-indigo) 12%, #fff 88%);
        color: var(--c-indigo);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .rw-step-content-icon--blue  { background: #eff6ff; color: #2563eb; }
    .rw-step-content-icon--amber { background: #fffbeb; color: #f59e0b; }
    .rw-step-content-icon--green { background: #ecfdf5; color: #10b981; }
    .rw-step-content-header:has(.rw-step-content-icon--amber) { border-left-color: var(--c-amber); background: #fffdf5; }
    .rw-step-content-header:has(.rw-step-content-icon--green) { border-left-color: var(--c-green); background: #f0fdf9; }
    .rw-step-content-header h5 {
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--c-ink);
        margin: 0 0 2px;
        letter-spacing: -0.01em;
    }
    .rw-step-content-header p {
        font-size: 0.78rem;
        color: var(--c-slate);
        margin: 0;
    }
    .ci-photo-card {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 14px;
        background: #fff;
    }
    .ci-photo-preview {
        margin-top: 10px;
        height: 130px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px dashed rgba(0,0,0,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .ci-photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .ci-photo-preview__placeholder {
        color: rgba(0,0,0,.55);
        font-size: .9rem;
    }
</style>
@stop

@section('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {

    const CHECKIN_MODELOS = @json($checkinModelosJson ?? []);
    const RESERVA_MODELO_ID = @json((int) ($reserva->modelo_id ?? 0));
    const RESERVA_MARCA_ID = @json((int) ($reserva->modelos->marca_id ?? 0));

    function rebuildModeloSelect(marcaId) {
        const $m = $('#modelo');
        $m.empty();
        $m.append(new Option('Selecione um modelo', '', true, true));
        $m.find('option:first').prop('disabled', true);
        if (!marcaId) {
            $m.val('').trigger('change');
            return;
        }
        CHECKIN_MODELOS.filter(o => String(o.marca_id) === String(marcaId))
            .forEach(o => $m.append(new Option(o.nome, o.id)));
        $m.val('').trigger('change');
    }

    function limparVeiculosSelect() {
        const $v = $('#veiculo');
        $v.empty();
        $v.append(new Option('Selecione marca e modelo', '', true, true));
        $v.find('option:first').prop('disabled', true);
        $v.val('').trigger('change');
    }

    function carregarVeiculosDisponiveisCheckin() {
        const marcaId = $('#marca').val();
        const modeloId = $('#modelo').val();
        const reserva_id = $('#reserva_id').val();
        const $v = $('#veiculo');

        if (!marcaId || !modeloId) {
            limparVeiculosSelect();
            return;
        }

        const url = "{{ route('reserva.checkin.veiculosDisponiveis', ':id') }}".replace(':id', reserva_id);
        $v.prop('disabled', true);

        $.getJSON(url, { marca_id: marcaId, modelo_id: modeloId })
                .done(function (data) {
                $v.empty();
                $v.append(new Option('Selecione um veiculo', '', true, true));
                $v.find('option:first').prop('disabled', true);
                if (!data.length) {
                    $v.append(new Option('Nenhum veiculo disponivel neste periodo', '', true, true));
                    $v.find('option:last').prop('disabled', true);
                } else {
                    data.forEach(row => {
                        const opt = new Option(row.nome + (row.placa ? ' — ' + row.placa : ''), row.id);
                        // attach useful data for JS autofill
                        if (row.quilometragem_atual !== undefined && row.quilometragem_atual !== null) opt.dataset.quilometragem = String(row.quilometragem_atual);
                        if (row.nivel_combustivel !== undefined && row.nivel_combustivel !== null) opt.dataset.nivelCombustivel = String(row.nivel_combustivel);
                        $v.append(opt);
                    });
                }
                $v.prop('disabled', false).val('').trigger('change');
            })
            .fail(function (xhr) {
                $v.prop('disabled', false);
                limparVeiculosSelect();
                const msg = xhr.responseJSON?.message || 'Erro ao carregar veiculos.';
                Swal.fire({ icon: 'error', title: msg });
            });
    }

    /* ========================
       SELECT2
    ======================== */
    $(document).ready(function () {
        $('.select2ci').select2();

        const marcaInicial = $('#marca').val();
        if (marcaInicial) {
            rebuildModeloSelect(marcaInicial);
            if (RESERVA_MODELO_ID && RESERVA_MARCA_ID && String(RESERVA_MARCA_ID) === String(marcaInicial)) {
                $('#modelo').val(String(RESERVA_MODELO_ID)).trigger('change');
                carregarVeiculosDisponiveisCheckin();
            }
        }

        $('#marca').on('change', function () {
            rebuildModeloSelect($(this).val());
            limparVeiculosSelect();
        });

        $('#modelo').on('change', function () {
            if ($(this).val()) {
                carregarVeiculosDisponiveisCheckin();
            } else {
                limparVeiculosSelect();
            }
        });

        $('#veiculo').change(function () {
            obterDanosDetectados();
            obterDanosExistentes();

            // Autofill quilometragem, combustivel e data/hora de saida
            const sel = document.getElementById('veiculo');
            const quilEl = document.getElementById('quilometragem');
            const combEl = document.getElementById('combustivel');
            const dataEl = document.getElementById('data_saida');
            const horaEl = document.getElementById('hora_saida');

            const opt = sel && sel.options && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex] : null;
            if (opt) {
                const km = opt.dataset.quilometragem ?? opt.getAttribute('data-quilometragem') ?? '';
                if (km !== '') {
                    // set value and min
                    if (quilEl) {
                        quilEl.value = km;
                        try { quilEl.min = String(km); } catch (e) {}
                    }
                }

                const nivel = opt.dataset.nivelCombustivel ?? opt.getAttribute('data-nivel-combustivel') ?? '';
                if (nivel !== '') {
                    if (combEl) {
                        // try to select option by text or value
                        let found = Array.from(combEl.options).find(o => String(o.value).toLowerCase() === String(nivel).toLowerCase() || String(o.text).toLowerCase() === String(nivel).toLowerCase());
                        if (found) combEl.value = found.value;
                    }
                } else {
                    // default to Cheio
                    if (combEl) {
                        const cheioOpt = Array.from(combEl.options).find(o => o.text.trim().toLowerCase() === 'cheio' || String(o.value).toLowerCase() === 'cheio');
                        if (cheioOpt) combEl.value = cheioOpt.value;
                    }
                }
            } else {
                // no option selected: ensure defaults
                if (combEl) {
                    const cheioOpt = Array.from(combEl.options).find(o => o.text.trim().toLowerCase() === 'cheio' || String(o.value).toLowerCase() === 'cheio');
                    if (cheioOpt) combEl.value = cheioOpt.value;
                }
            }

            // set date/time to now by default
            const now = new Date();
            if (dataEl && !dataEl.value) {
                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, '0');
                const dd = String(now.getDate()).padStart(2, '0');
                dataEl.value = `${yyyy}-${mm}-${dd}`;
            }
            if (horaEl && !horaEl.value) {
                const hh = String(now.getHours()).padStart(2, '0');
                const mi = String(now.getMinutes()).padStart(2, '0');
                horaEl.value = `${hh}:${mi}`;
            }
        });

        // Prevent entering smaller quilometragem than min
        const quilInput = document.getElementById('quilometragem');
        if (quilInput) {
            quilInput.addEventListener('input', function () {
                const min = this.min ? Number(this.min) : null;
                const val = this.value ? Number(this.value) : null;
                if (min !== null && val !== null && val < min) {
                    this.value = String(min);
                }
            });
        }
    });

    /* ========================
       WIZARD STEPS
    ======================== */
    const contraModoEdicao = {{ $contratoExistente ? 'true' : 'false' }};

    const PHOTO_INPUT_IDS = [
        'foto_direita',
        'foto_esquerda',
        'foto_frente',
        'foto_tras',
        'foto_velocimetro',
        'foto_combustivel',
    ];

    const stepBtns   = Array.from(document.querySelectorAll('.step-btn'));
    const stepPanels = Array.from(document.querySelectorAll('.wizard-step[data-step]'));
    const prevBtn    = document.getElementById('wizardPrev');
    const nextBtn    = document.getElementById('wizardNext');
    const createBtn  = document.getElementById('wizardCreateBtn');
    let current      = 1;
    const lastStep   = stepPanels.length;

    function setupPhotoPreviews() {
        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            const preview = document.querySelector(`.ci-photo-preview[data-preview-for="${id}"]`);
            if (!input || !preview) return;

            let objectUrl = null;
            input.addEventListener('change', function () {
                input.classList.remove('is-invalid');

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                if (this.files && this.files[0]) {
                    objectUrl = URL.createObjectURL(this.files[0]);
                    preview.innerHTML = `<img src="${objectUrl}" alt="Preview">`;
                } else {
                    preview.innerHTML = `
                        <span class="ci-photo-preview__placeholder">
                            <i class="far fa-image mr-1"></i>Sem imagem
                        </span>
                    `;
                }
            });
        });
    }

    setupPhotoPreviews();

    function showStep(n) {
        current = Math.min(Math.max(n, 1), lastStep);

        stepPanels.forEach(panel => {
            panel.classList.toggle('active', Number(panel.dataset.step) === current);
        });

        stepBtns.forEach(btn => {
            const t = Number(btn.dataset.stepTarget);
            btn.classList.toggle('active', t === current);
            btn.classList.toggle('completed', t < current);
        });

        prevBtn.style.visibility = current === 1 ? 'hidden' : 'visible';
        nextBtn.style.display    = current === lastStep ? 'none' : 'inline-flex';
        createBtn.classList.toggle('d-none', current !== lastStep);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep1(showAlert = true) {
        const campos = document.querySelectorAll('#form_dados_veiculos input:required, #form_dados_veiculos select:required');
        const camposInvalidos = [];

        campos.forEach(campo => {
            const invalido = !campo.value || (campo.classList.contains('select2-hidden-accessible') && !$(campo).val());
            campo.classList.toggle('is-invalid', invalido);
            if (invalido) camposInvalidos.push(campo.name);
        });

        if (camposInvalidos.length > 0) {
            if (showAlert) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos obrigatorios nao preenchidos',
                    html: `<ul style="text-align:left">${camposInvalidos.map(c => `<li>${String(c).replace(/_/g, ' ')}</li>`).join('')}</ul>`,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                    toast: false,
                });
            }
            return false;
        }

        // Verifica que um veículo foi selecionado (campo hidden `veiculo`)
        const veiculoHidden = document.getElementById('veiculo');
        const veiculoDisplay = document.getElementById('veiculo_display');
        if (!veiculoHidden || !veiculoHidden.value) {
            if (veiculoDisplay) veiculoDisplay.classList.add('is-invalid');
            if (showAlert) {
                Swal.fire({ icon: 'error', title: 'Veículo não selecionado', text: 'Selecione um veículo para prosseguir.' });
            }
            return false;
        } else {
            if (veiculoDisplay) veiculoDisplay.classList.remove('is-invalid');
        }

        // valida quilometragem minima antes de aceitar o passo
        if (!validarQuilometragemMin()) {
            return false;
        }

        return true;
    }

    // adicional: valida quilometragem minimo
    function validarQuilometragemMin() {
        const quil = document.getElementById('quilometragem');
        if (!quil) return true;
        const min = quil.min ? Number(quil.min) : null;
        const val = quil.value ? Number(quil.value) : null;
        if (min !== null && val !== null && val < min) {
            quil.classList.add('is-invalid');
            Swal.fire({ icon: 'error', title: 'Quilometragem inválida', html: `A quilometragem informada não pode ser menor que a atual (${min}).` });
            return false;
        }
        quil.classList.remove('is-invalid');
        return true;
    }

    function validateStep2Photos(showAlert = true) {
        // Em modo edição, as fotos já existem — não obriga re-upload
        if (contraModoEdicao) return true;

        const invalidos = [];

        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            const invalido = !input || !input.files || input.files.length === 0;
            if (input) input.classList.toggle('is-invalid', invalido);
            if (invalido) invalidos.push(id);
        });

        if (invalidos.length > 0) {
            if (showAlert) {
                Swal.fire({
                    icon: 'error',
                    title: 'Fotos obrigatorias nao enviadas',
                    html: `<div style="text-align:left">Envie todas as fotos do Passo 2 para continuar.</div>`,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                    toast: false,
                });
            }
            return false;
        }

        return true;
    }

    function goToStep(target) {
        // Voltar nunca bloqueia; avancar exige concluir os passos anteriores.
        if (target <= current) {
            showStep(target);
            return;
        }

        if (target >= 2 && !validateStep1(true)) {
            showStep(1);
            return;
        }

        if (target >= 3 && !validateStep2Photos(true)) {
            showStep(2);
            return;
        }

        showStep(target);
    }

    stepBtns.forEach(btn => {
        btn.addEventListener('click', () => goToStep(Number(btn.dataset.stepTarget)));
    });

    prevBtn.addEventListener('click', () => showStep(current - 1));
    nextBtn.addEventListener('click', () => goToStep(current + 1));

    /* ========================
       CRIAR CONTRATO
    ======================== */
    function triggerCriarContrato() {
        if (!validateStep1(true)) {
            showStep(1);
            return;
        }
        if (!validateStep2Photos(true)) {
            showStep(2);
            return;
        }

        criarContrato();
    }

    document.getElementById('btn_criar_contrato')?.addEventListener('click', triggerCriarContrato);
    createBtn?.addEventListener('click', triggerCriarContrato);

    document.getElementById('form_dados_veiculos').addEventListener('submit', function (event) {
        event.preventDefault();
        if (!validateStep1(true)) {
            showStep(1);
            return;
        }

        // Enter no Passo 1 avanca para o Passo 2.
        goToStep(2);
    });

    function criarContrato() {
        const reserva_id = document.getElementById('reserva_id').value;
        const url = "{{ route('reserva.criarContratoCheckin', ':id') }}".replace(':id', reserva_id);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const contrato = {
            marca_id:           document.getElementById('marca')?.value          || null,
            modelo_id:          document.getElementById('modelo')?.value         || null,
            veiculo_id:         document.getElementById('veiculo')?.value        || null,
            quilometragem_atual: document.getElementById('quilometragem')?.value || null,
            nivel_combustivel:  document.getElementById('combustivel')?.value    || null,
            data_saida:         document.getElementById('data_saida')?.value     || null,
            hora_saida:         document.getElementById('hora_saida')?.value     || null,
            notas_saida:        document.getElementById('notas_saida')?.value    || null,
            observacoes_saida:  document.getElementById('observacoes_saida')?.value || null,
        };

        const fd = new FormData();
        Object.entries(contrato).forEach(([k, v]) => fd.append(k, v ?? ''));
        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            if (input && input.files && input.files[0]) {
                fd.append(id, input.files[0]);
            }
        });

        // Se for upgrade, envia motivo_upgrade para auditoria
        const selectedOpt = document.querySelector('#veiculo option:checked');
        const isUpgrade = selectedOpt?.dataset?.upgrade === '1';
        if (isUpgrade) {
            const motivo = document.getElementById('motivo_upgrade_input')?.value || '';
            fd.append('motivo_upgrade', motivo);
        }

        const createBtnEl = document.getElementById('btn_criar_contrato');
        const wizBtnEl    = document.getElementById('wizardCreateBtn');
        [createBtnEl, wizBtnEl].forEach(b => {
            if (b) {
                b.disabled = true;
                b._originalHtml = b.innerHTML;
                b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            }
        });

        $.ajax({
            url,
            type: 'POST',
            data: fd,
            headers: { 'X-CSRF-TOKEN': csrf },
            processData: false,
            contentType: false,
            success: function (data) {
                Swal.fire({
                    icon: 'success',
                    title: contraModoEdicao ? 'Contrato atualizado com sucesso!' : 'Contrato criado com sucesso!',
                    showConfirmButton: false,
                    timer: 2500,
                    toast: true,
                    position: 'top-right',
                });
                setTimeout(() => {
                    window.location.href = "{{ route('reserva.show', ':id') }}".replace(':id', reserva_id);
                }, 2600);
            },
            error: function (xhr) {
                let title = 'Erro ao criar contrato';
                let html = 'Verifique os dados e tente novamente.';

                if (xhr && xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    title = 'Validacao falhou';
                    const msgs = Object.values(xhr.responseJSON.errors).flat();
                    html = `<ul style=\"text-align:left\">${msgs.map(m => `<li>${m}</li>`).join('')}</ul>`;
                } else if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    html = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title,
                    html,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                });
                [createBtnEl, wizBtnEl].forEach(b => { if (b) { b.disabled = false; b.innerHTML = b._originalHtml || b.innerHTML; } });
            }
        });
    }

    /* ========================
       DANOS
    ======================== */
    function rowImagensDano(dano) {
        const imgs = dano.imagens || [];
        if (!imgs.length) return '<span class="text-muted" style="font-size:.75rem">Sem foto</span>';
        return imgs.map(i => {
            const src = `{{ asset('storage') }}/${i.imagem}`;
            return `<img src="${src}" alt="Dano"
                style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:2px solid #e2e9f4;cursor:zoom-in;margin:2px"
                onclick="abrirFotoDano('${src}')"
                onerror="this.style.display='none'">`;
        }).join('');
    }

    function abrirFotoDano(src) {
        let modal = document.getElementById('lightboxFotoDano');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'lightboxFotoDano';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:zoom-out';
            modal.onclick = () => modal.remove();
            modal.innerHTML = '<img id="lightboxFotoDanoImg" style="max-width:92vw;max-height:90vh;border-radius:12px;box-shadow:0 8px 40px rgba(0,0,0,.6)">';
            document.body.appendChild(modal);
        }
        document.getElementById('lightboxFotoDanoImg').src = src;
        document.getElementById('lightboxFotoDano').style.display = 'flex';
    }

    function obterDanosDetectados() {
        const veiculo_id = $('#veiculo').val();
        const reserva_id = $('#reserva_id').val();

        $.ajax({
            url: "{{ route('dano.obterDanosDetectados') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_detectados');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="damage-empty"><i class="fas fa-shield-alt"></i>Nenhum dano detectado</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    let extra = '';
                    if (dano.correcao_realizada) {
                        extra = `<br><small class="text-success"><i class="fas fa-wrench"></i> Corrigido: ${dano.correcao_descricao ?? ''} (${dano.correcao_data ?? ''})</small>`;
                    }
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}${extra}</td>
                            <td>${rowImagensDano(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    function obterDanosExistentes() {
        const veiculo_id = $('#veiculo').val();
        const reserva_id = $('#reserva_id').val();

        $.ajax({
            url: "{{ route('dano.obterDanosExistentes') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_existentes');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="damage-empty"><i class="fas fa-check-circle"></i>Nenhum dano anterior registrado</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    let extra = '';
                    if (dano.correcao_realizada) {
                        extra = `<br><small class="text-success"><i class="fas fa-wrench"></i> Corrigido: ${dano.correcao_descricao ?? ''} (${dano.correcao_data ?? ''})</small>`;
                    } else {
                        extra = `<br><small class="text-warning"><i class="fas fa-hourglass-half"></i> Correção pendente</small>`;
                    }
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}${extra}</td>
                            <td>${rowImagensDano(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    /* Expor globalmente para o modal_dano_checkin usar */
    window.obterDanosDetectados = obterDanosDetectados;
    window.obterDanosExistentes = obterDanosExistentes;

    function setNomeDano(nome) {
        const el = document.getElementById('informacao_dano');
        if (el) el.innerText = nome;
    }
    window.setNomeDano = setNomeDano;

    /* Init */
    showStep(1);

    // Em modo edição, carrega danos do veículo já selecionado
    @if($contratoExistente && $contratoExistente->veiculo_id)
    (function() {
        setTimeout(function() {
            obterDanosExistentes();
            obterDanosDetectados();
        }, 300);
    })();
    @endif

    // Contrato assinado: bloqueia todos os campos e botões do wizard
    @if($contratoExistente && $contratoExistente->assinado == 'S')
    (function() {
        const form = document.getElementById('form_dados_veiculos');
        if (form) {
            form.querySelectorAll('input, select, textarea, button').forEach(function(el) {
                el.disabled = true;
            });
        }
        // Oculta botões de navegação do wizard (Continuar/Salvar)
        const nextBtn  = document.getElementById('wizardNext');
        const prevBtn  = document.getElementById('wizardPrev');
        const createBtn = document.getElementById('wizardCreateBtn');
        [nextBtn, createBtn].forEach(function(b) { if (b) b.classList.add('d-none'); });
        // Mantém o botão Voltar funcional mas apenas para navegar entre steps já preenchidos
    })();
    @endif

    /* ========================
       UPGRADE DE CATEGORIA
    ======================== */
    (function initUpgradeCheck() {
        const statusUpgradeUrl = @json(route('reserva.checkin.statusUpgrade', $reserva->id));

        fetch(statusUpgradeUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.tem_disponibilidade_categoria_original) return; // sem upgrade

                const banner = document.getElementById('upgrade-banner');
                const grupoLabel = document.getElementById('upgrade-grupo-original');
                if (banner) {
                    banner.classList.remove('d-none');
                    if (grupoLabel) grupoLabel.textContent = data.grupo_original?.nome ?? 'categoria original';
                }

                // Ajusta UI do passo 1: ocultar marca/modelo e focar no veiculo disponivel no periodo
                const hintNormal = document.getElementById('identificacao_hint_normal');
                const hintUpgrade = document.getElementById('identificacao_hint_upgrade');
                if (hintNormal) hintNormal.classList.add('d-none');
                if (hintUpgrade) hintUpgrade.classList.remove('d-none');

                const marcaCol = document.getElementById('marca_col');
                const modeloCol = document.getElementById('modelo_col');
                const veiculoCol = document.getElementById('veiculo_col');
                if (marcaCol) marcaCol.classList.add('d-none');
                if (modeloCol) modeloCol.classList.add('d-none');
                if (veiculoCol) {
                    veiculoCol.classList.remove('col-md-4');
                    veiculoCol.classList.add('col-md-12');
                }

                const marcaEl = document.getElementById('marca');
                const modeloEl = document.getElementById('modelo');
                if (marcaEl) { marcaEl.required = false; marcaEl.disabled = true; }
                if (modeloEl) { modeloEl.required = false; modeloEl.disabled = true; }

                // Substitui o select de veiculo por um select de upgrade (somente elegiveis por padrao)
                const veiculoSelect = document.getElementById('veiculo');
                if (!veiculoSelect) return;

                const todos = Array.isArray(data.veiculos_periodo) ? data.veiculos_periodo : [];
                if (!todos.length) {
                    veiculoSelect.innerHTML = '<option value="" disabled selected>Nenhum veiculo disponivel no periodo</option>';
                    veiculoSelect.disabled = true;
                    return;
                }

                const mostrarBloqueadosEl = document.getElementById('upgrade_mostrar_bloqueados');

                const gruposMap = new Map();
                todos.forEach(v => {
                    const grupoId = v.grupo?.id ?? 0;
                    if (!gruposMap.has(grupoId)) {
                        gruposMap.set(grupoId, { grupo: v.grupo ?? { id: 0, nome: 'Sem grupo', ordem: 0 }, veiculos: [] });
                    }
                    gruposMap.get(grupoId).veiculos.push(v);
                });

                const gruposOrdenados = Array.from(gruposMap.values()).sort((a, b) => {
                    const oa = Number(a.grupo?.ordem ?? 0);
                    const ob = Number(b.grupo?.ordem ?? 0);
                    return ob - oa; // melhores primeiro
                });

                const eligibleGroups = [];
                const blockedGroups = [];

                gruposOrdenados.forEach(item => {
                    const veiculos = item.veiculos ?? [];
                    const elegiveis = veiculos.filter(v => !!v.elegivel_upgrade);
                    const bloqueados = veiculos.filter(v => !v.elegivel_upgrade);

                    if (elegiveis.length) eligibleGroups.push({ ...item, veiculos: elegiveis });
                    if (bloqueados.length) blockedGroups.push({ ...item, veiculos: bloqueados });
                });

                function buildOptions(includeBlocked) {
                    veiculoSelect.disabled = false;
                    veiculoSelect.innerHTML = '<option value="" disabled selected>Selecione um veículo</option>';

                    if (!eligibleGroups.length) {
                        veiculoSelect.innerHTML = '<option value="" disabled selected>Nenhum veículo elegível para upgrade</option>';
                        veiculoSelect.disabled = true;
                        return;
                    }

                    eligibleGroups.forEach(item => {
                        const optGroup = document.createElement('optgroup');
                        optGroup.label = `⬆ ${item.grupo?.nome ?? 'Grupo'} (upgrade)`;
                        (item.veiculos ?? []).forEach(v => {
                            const opt = document.createElement('option');
                            opt.value = v.id;
                                opt.textContent = (v.nome ?? 'Veículo') + (v.placa ? ' - ' + v.placa : '');
                                if (v.quilometragem_atual !== undefined && v.quilometragem_atual !== null) opt.dataset.quilometragem = String(v.quilometragem_atual);
                                if (v.nivel_combustivel !== undefined && v.nivel_combustivel !== null) opt.dataset.nivelCombustivel = String(v.nivel_combustivel);
                            opt.dataset.upgrade = '1';
                            optGroup.appendChild(opt);
                        });
                        veiculoSelect.appendChild(optGroup);
                    });

                    if (includeBlocked && blockedGroups.length) {
                        blockedGroups.forEach(item => {
                            const optGroup = document.createElement('optgroup');
                            optGroup.label = `⬇ ${item.grupo?.nome ?? 'Grupo'} (inferior - bloqueado)`;
                            (item.veiculos ?? []).forEach(v => {
                                const opt = document.createElement('option');
                                opt.value = v.id;
                                opt.textContent = (v.nome ?? 'Veículo') + (v.placa ? ' - ' + v.placa : '') + ' (inferior)';
                                opt.disabled = true;
                                optGroup.appendChild(opt);
                            });
                            veiculoSelect.appendChild(optGroup);
                        });
                    }

                    // refresh select2
                    try { $('#veiculo').trigger('change.select2'); } catch (e) {}
                }

                buildOptions(!!mostrarBloqueadosEl?.checked);
                mostrarBloqueadosEl?.addEventListener('change', function () {
                    buildOptions(!!this.checked);
                });

                return;

                veiculoSelect.innerHTML = '<option value="" disabled selected>Selecione veiculo de upgrade</option>';
                (data.grupos_upgrade ?? []).forEach(item => {
                    const optGroup = document.createElement('optgroup');
                    optGroup.label = '\u2B06 ' + item.grupo.nome + ' (upgrade)';
                    (item.veiculos ?? []).forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = v.nome + (v.placa ? ' — ' + v.placa : '');
                        opt.dataset.upgrade = '1';
                        optGroup.appendChild(opt);
                    });
                    veiculoSelect.appendChild(optGroup);
                });
            })
            .catch(() => { /* silencioso — funciona sem upgrade */ });
    })();

})();
</script>
@stop

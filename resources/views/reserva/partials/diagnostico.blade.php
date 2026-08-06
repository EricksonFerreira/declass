@php
    $verde = '#059669'; $cinza = '#64748b'; $laranja = '#ea580c';
    $disp = collect($veiculos)->where('disponivel', true);
    $ind = collect($veiculos)->where('disponivel', false);
@endphp

<div id="diagRoot" data-count="{{ count($veiculos) }}">
    <div class="diag-summary d-flex align-items-center gap-2 mb-3 flex-wrap">
        <span class="badge badge-success p-2" style="font-size:.82rem;"><i class="fas fa-check-circle mr-1"></i>{{ $disp->count() }} disponivel</span>
        <span class="badge badge-secondary p-2" style="font-size:.82rem;"><i class="fas fa-times-circle mr-1"></i>{{ $ind->count() }} indisponivel</span>
        <span class="badge badge-info p-2" style="font-size:.82rem;"><i class="fas fa-car mr-1"></i>{{ count($veiculos) }} total</span>
        @if($reserva_info ?? null)
            <a href="{{ $reserva_info['url'] ?? '#' }}" target="_blank" class="badge badge-light p-2" style="font-size:.82rem;border:1px solid #dee2e6;">
                <i class="fas fa-external-link-alt mr-1"></i>Reserva #{{ $reserva_info['id'] ?? '' }}
            </a>
        @endif
    </div>

    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="row align-items-end">
                <div class="col-md-3 form-group mb-0"><label class="small mb-1">Nome</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="nome" placeholder="Ex: Corolla" oninput="filtrarDiagnostico()"></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Placa</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="placa" placeholder="ABC-1234" oninput="filtrarDiagnostico()"></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Disponibilidade</label><select class="form-control form-control-sm diag-filtro" data-col="disponivel" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="1">Disponivel</option><option value="0">Indisponivel</option></select></div>
                <div class="col-md-4 form-group mb-0 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limparFiltrosDiag()" title="Limpar"><i class="fas fa-eraser"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-info ml-1" onclick="toggleFiltrosAvancados()"><i class="fas fa-sliders-h"></i> <span>Filtros Avancados</span></button>
                </div>
            </div>
            <div id="diagFiltrosAvancados" class="row" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0;">
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Modelo</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="modelo" placeholder="Ex: Civic" oninput="filtrarDiagnostico()"></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Estacao</label><select class="form-control form-control-sm diag-filtro" data-col="estacao" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Status Veiculo</label><select class="form-control form-control-sm diag-filtro" data-col="status" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Reservas</label><select class="form-control form-control-sm diag-filtro" data-col="reservas" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Movimentacoes</label><select class="form-control form-control-sm diag-filtro" data-col="movimentacoes" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
            </div>
        </div>
    </div>

    <div id="diagLista" class="diag-lista">
        @foreach($veiculos as $v)
            @php
                $regras = $v['regras'] ?? [];
                $r = [];
                foreach ($regras as $rr) { $r[$rr['nome']] = $rr; }
                $ordem = ['EstaÃ§Ã£o','Disponibilidade','Status','Reservas','MovimentaÃ§Ãµes'];
                $isDisp = $v['disponivel'];
                $grpOk = count(array_filter($regras, fn($rr) => $rr['ok']));
                $grpTot = count($regras);
                $accent = $isDisp ? $verde : $cinza;
                $borderC = $isDisp ? '#86efac' : '#e5eaf2';
                $sTxt = $isDisp ? 'Disponivel' : 'Indisponivel';
                $sBg = $isDisp ? 'rgba(16,185,129,.15)' : 'rgba(100,116,139,.13)';
                $sCo = $isDisp ? $verde : $cinza;
                $rStatus = [];
                foreach ($regras as $rr) { $rStatus[$rr['nome']] = $rr['ok'] ? '1' : '0'; }
                $parts = preg_split('/\s+/', trim($v['nome'] ?? '??'));
                $initials = mb_substr($parts[0] ?? '?', 0, 1) . mb_substr($parts[1] ?? '', 0, 1);
            @endphp

            <div class="diag-card"
                data-nome="{{ strtolower($v['nome'] ?? '') }}"
                data-placa="{{ strtolower($v['placa'] ?? '') }}"
                data-modelo="{{ strtolower($v['modelo'] ?? '') }}"
                data-disponivel="{{ $isDisp ? '1' : '0' }}"
                data-estacao="{{ $rStatus['Estacao'] ?? '0' }}"
                data-status="{{ $rStatus['Status'] ?? '0' }}"
                data-reservas="{{ $rStatus['Reservas'] ?? '0' }}"
                data-movimentacoes="{{ $rStatus['Movimentacoes'] ?? '0' }}"
                style="--diag-border: {{ $borderC }}; --diag-accent: {{ $accent }};">
                <div class="diag-card__shell">
                    <div class="diag-card__topbar"></div>
                    <div class="diag-card__head">
                        <div class="diag-card__avatar">{{ $initials }}</div>
                        <div class="diag-card__identity">
                            <div class="diag-card__eyebrow">#{{ $v['id'] }} · {{ $v['placa'] ?? '---' }}</div>
                            <div class="diag-card__title">{{ $v['nome'] }}</div>
                            <div class="diag-card__subtitle">{{ $v['modelo'] ?? '---' }} · {{ $v['cor'] ?? '---' }}</div>
                        </div>
                        <div class="diag-card__status">
                            <span class="diag-pill" style="background:{{ $sBg }};color:{{ $sCo }};">{{ $sTxt }}</span>
                            <span class="diag-pill diag-pill--score" style="background:{{ $grpOk === $grpTot ? 'rgba(16,185,129,.12)' : 'rgba(245,158,11,.15)' }};color:{{ $grpOk === $grpTot ? $verde : $laranja }};">{{ $grpOk }}/{{ $grpTot }}</span>
                        </div>
                    </div>

                    <div class="diag-card__rules">
                        @foreach($ordem as $nk)
                            @if(!isset($r[$nk])) @continue @endif
                            @php
                                $rr = $r[$nk];
                                $ok = $rr['ok'];
                                $icC = $ok ? $verde : '#dc2626';
                            @endphp
                            <div class="diag-rule">
                                <i class="fas fa-{{ $ok ? 'check-circle' : 'times-circle' }}" style="color:{{ $icC }};"></i>
                                <div class="diag-rule__content">
                                    <strong>{{ $nk }}</strong>
                                    <div>{!! $rr['info'] ?? '' !!}</div>
                                    @if(!$ok && ($rr['solucao'] ?? null))
                                        <div class="diag-rule__solution"><i class="fas fa-lightbulb mr-1"></i>{{ $rr['solucao'] }}</div>
                                    @endif
                                    @if($rr['itens'] ?? null)
                                        <div class="diag-rule__links">
                                            @foreach($rr['itens'] as $item)
                                                @php $ic2 = $item['tipo'] === 'reserva' ? 'fa-calendar-alt' : 'fa-truck-moving'; @endphp
                                                <a href="{{ $item['url'] }}" target="_blank">
                                                    <i class="fas {{ $ic2 }}"></i>{{ $item['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="diag-card__footer">
                        <span><i class="fas fa-check-circle" style="color:{{ $verde }};"></i>{{ $grpOk }} ok</span>
                        <span><i class="fas fa-times-circle" style="color:#dc2626;"></i>{{ $grpTot - $grpOk }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

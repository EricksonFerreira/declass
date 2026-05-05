@php
    $verde = '#059669'; $cinza = '#64748b'; $laranja = '#ea580c';
    $disp = collect($veiculos)->where('disponivel', true);
    $ind = collect($veiculos)->where('disponivel', false);
@endphp
<div id="diagRoot" data-count="{{ count($veiculos) }}">
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
<span class="badge badge-success p-2" style="font-size:.82rem;"><i class="fas fa-check-circle mr-1"></i>{{ $disp->count() }} disponivel</span>
<span class="badge badge-secondary p-2" style="font-size:.82rem;"><i class="fas fa-times-circle mr-1"></i>{{ $ind->count() }} indisponivel</span>
<span class="badge badge-info p-2" style="font-size:.82rem;"><i class="fas fa-car mr-1"></i>{{ count($veiculos) }} total</span>
@if($reserva_info ?? null)<a href="{{ $reserva_info['url'] ?? '#' }}" target="_blank" class="badge badge-light p-2" style="font-size:.82rem;border:1px solid #dee2e6;"><i class="fas fa-external-link-alt mr-1"></i>Reserva #{{ $reserva_info['id'] ?? '' }}</a>@endif
</div>

<div class="card mb-2"><div class="card-body py-2">
<div class="row align-items-end">
<div class="col-md-3 form-group mb-0"><label class="small mb-1">Nome</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="nome" placeholder="Ex: Corolla" oninput="filtrarDiagnostico()"></div>
<div class="col-md-2 form-group mb-0"><label class="small mb-1">Placa</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="placa" placeholder="ABC-1234" oninput="filtrarDiagnostico()"></div>
<div class="col-md-2 form-group mb-0"><label class="small mb-1">Disponibilidade</label><select class="form-control form-control-sm diag-filtro" data-col="disponivel" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="1">Disponivel</option><option value="0">Indisponivel</option></select></div>
<div class="col-md-2 form-group mb-0 d-flex align-items-end gap-1">
<button type="button" class="btn btn-sm btn-outline-secondary" onclick="limparFiltrosDiag()" title="Limpar"><i class="fas fa-eraser"></i></button>
<button type="button" class="btn btn-sm btn-outline-info" onclick="toggleFiltrosAvancados()"><i class="fas fa-sliders-h"></i> <span>Filtros Avancados</span></button>
</div></div>
<div id="diagFiltrosAvancados" class="row" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0;">
<div class="col-md-2 form-group mb-0"><label class="small mb-1">Modelo</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="modelo" placeholder="Ex: Civic" oninput="filtrarDiagnostico()"></div>
<div class="col-md-2 form-group mb-0"><label class="small mb-1">Estacao</label><select class="form-control form-control-sm diag-filtro" data-col="estacao" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
<div class="col-md-2 form-group mb-0"><label class="small mb-1">Status Veiculo</label><select class="form-control form-control-sm diag-filtro" data-col="status" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
<div class="col-md-2 form-group mb-0"><label class="small mb-1">Reservas</label><select class="form-control form-control-sm diag-filtro" data-col="reservas" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
<div class="col-md-2 form-group mb-0"><label class="small mb-1">Movimentacoes</label><select class="form-control form-control-sm diag-filtro" data-col="movimentacoes" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
</div></div></div>

<div id="diagLista" style="display:flex;flex-wrap:wrap;gap:12px;">
@foreach($veiculos as $v)
    @php
        $regras = $v['regras'] ?? []; $r = []; foreach ($regras as $rr) { $r[$rr['nome']] = $rr; }
        $ordem = ['Estação','Disponibilidade','Status','Reservas','Movimentações'];
        $isDisp = $v['disponivel']; $grpOk = count(array_filter($regras, fn($rr) => $rr['ok'])); $grpTot = count($regras);
        $accent = $isDisp ? $verde : $cinza; $borderC = $isDisp ? '#86efac' : '#e5eaf2'; $sTxt = $isDisp ? 'Disponivel' : 'Indisponivel';
        $sBg = $isDisp ? 'rgba(16,185,129,.15)' : 'rgba(100,116,139,.13)'; $sCo = $isDisp ? $verde : $cinza;
        $rStatus = []; foreach ($regras as $rr) { $rStatus[$rr['nome']] = $rr['ok'] ? '1' : '0'; }
        $parts = preg_split('/\s+/', trim($v['nome'] ?? '??')); $initials = mb_substr($parts[0] ?? '?',0,1) . mb_substr($parts[1] ?? '',0,1);
    @endphp
    <div class="diag-card" data-nome="{{ strtolower($v['nome']??'') }}" data-placa="{{ strtolower($v['placa']??'') }}" data-modelo="{{ strtolower($v['modelo']??'') }}" data-disponivel="{{ $isDisp?'1':'0' }}" data-estacao="{{ $rStatus['Estacao']??'0' }}" data-status="{{ $rStatus['Status']??'0' }}" data-reservas="{{ $rStatus['Reservas']??'0' }}" data-movimentacoes="{{ $rStatus['Movimentacoes']??'0' }}" style="width:calc(25% - 9px);flex:0 0 calc(25% - 9px);box-sizing:border-box;">
    <div style="border:1px solid {{ $borderC }};border-radius:14px;overflow:hidden;background:#fff;">
    <div style="height:5px;background:linear-gradient(90deg,{{ $accent }},{{ $isDisp ? '#34d399' : '#94a3b8' }});"></div>
    <div style="display:flex;align-items:flex-start;gap:11px;padding:14px 16px 12px;border-bottom:1px solid #f1f5f9;">
    <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;font-size:.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $initials }}</div>
    <div style="flex:1;min-width:0;"><div style="font-size:.72rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">#{{ $v['id'] }} . {{ $v['placa'] ?? '---' }}</div>
    <div style="font-size:.97rem;font-weight:700;color:#0f2740;">{{ $v['nome'] }}</div>
    <div style="font-size:.78rem;color:#64748b;">{{ $v['modelo'] ?? '---' }} . {{ $v['cor'] ?? '---' }}</div></div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
    <span style="padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;background:{{ $sBg }};color:{{ $sCo }};">{{ $sTxt }}</span>
    <span style="padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:600;background:{{ $grpOk===$grpTot?'rgba(16,185,129,.12)':'rgba(245,158,11,.15)' }};color:{{ $grpOk===$grpTot?$verde:$laranja }};">{{ $grpOk }}/{{ $grpTot }}</span>
    </div></div>
    <div style="padding:10px 16px 14px;font-size:.78rem;">
    @foreach($ordem as $nk)
        @if(!isset($r[$nk])) @continue @endif
        @php $rr = $r[$nk]; $ok = $rr['ok']; $icC = $ok ? $verde : '#dc2626'; @endphp
        <div style="display:flex;align-items:flex-start;gap:8px;padding:4px 0;">
        <i class="fas fa-{{ $ok?'check-circle':'times-circle' }}" style="color:{{ $icC }};font-size:.85rem;margin-top:2px;flex-shrink:0;width:16px;"></i>
        <div style="flex:1;"><strong>{{ $nk }}</strong>
        <div style="color:#475569;">{!! $rr['info'] ?? '' !!}</div>
        @if(!$ok && ($rr['solucao'] ?? null))
        <div style="font-size:.75rem;color:#dc2626;margin-top:2px;padding:3px 6px;background:#fef2f2;border-radius:4px;border:1px solid #fca5a5;"><i class="fas fa-lightbulb mr-1"></i>{{ $rr['solucao'] }}</div>
        @endif
        @if($rr['itens'] ?? null)
        <div style="margin-top:3px;display:flex;flex-wrap:wrap;gap:4px;">
        @foreach($rr['itens'] as $item)
            @php $ic2 = $item['tipo']==='reserva'?'fa-calendar-alt':'fa-truck-moving'; @endphp
            <a href="{{ $item['url'] }}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;font-size:.72rem;font-weight:600;border-radius:20px;border:1px solid #3b82f6;color:#2563eb;text-decoration:none;transition:all .15s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''"><i class="fas {{ $ic2 }}" style="font-size:.65rem;"></i>{{ $item['label'] }}</a>
        @endforeach
        </div>
        @endif
        </div></div>
    @endforeach
    </div>
    <div style="display:flex;gap:6px;padding:10px 16px;background:#fafbfc;border-top:1px solid #f1f5f9;">
    <span style="font-size:.72rem;color:#64748b;"><i class="fas fa-check-circle mr-1" style="color:{{ $verde }};"></i>{{ $grpOk }} ok</span>
    <span style="font-size:.72rem;color:#64748b;"><i class="fas fa-times-circle mr-1" style="color:#dc2626;"></i>{{ $grpTot - $grpOk }}</span>
    </div></div></div>
@endforeach
</div></div>

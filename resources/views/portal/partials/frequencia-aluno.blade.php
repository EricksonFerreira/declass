{{--
    Bloco de frequencia de uma matricula: card com cards coloridos por
    disciplina (anel de presenca) + modais com o historico de aulas.
    Usado na tela Frequencia do portal e na aba Frequencia do hub
    da turma. Espera: $bloco = ['matricula', 'disciplinas', 'percentualGeral'].
--}}
@php
    $corFrequencia = fn ($v) => $v >= 75 ? 'success' : ($v >= 60 ? 'warning' : 'danger');
    $corStatus = ['presente' => 'success', 'falta' => 'danger', 'justificada' => 'warning', 'abonada' => 'info'];
    $corHex = ['success' => '#28a745', 'warning' => '#e0a800', 'danger' => '#dc3545', 'info' => '#17a2b8', 'secondary' => '#6c757d'];
    $corSoft = ['success' => '#e9f7ef', 'warning' => '#fdf3d7', 'danger' => '#fdecea', 'info' => '#e5f6fb', 'secondary' => '#eef0f2'];
    $iconeStatus = ['presente' => 'fa-check', 'falta' => 'fa-times', 'justificada' => 'fa-file-alt', 'abonada' => 'fa-star'];
    $mesesAbrev = [1 => 'jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
    $diasSemana = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
    $matricula = $bloco['matricula'];
    $disciplinas = $bloco['disciplinas'];
    $percentualGeral = $bloco['percentualGeral'];
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <span class="font-weight-bold">
            <i class="fas fa-user-graduate mr-1"></i>
            {{ $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome }}
            <small class="text-muted font-weight-normal">· {{ $matricula->turma?->nome ?? 'Sem turma' }} · {{ $matricula->turma?->anoLetivo->ano ?? '' }}</small>
        </span>
        @if($percentualGeral !== null)
            <span class="badge badge-{{ $corFrequencia($percentualGeral) }}">Presença geral {{ $percentualGeral }}%</span>
        @endif
    </div>
    <div class="card-body">
        <div class="home-launcher">
            <div class="row">
                @forelse($disciplinas as $linha)
                    @php
                        $cor = $linha['percentual'] !== null ? $corFrequencia($linha['percentual']) : 'secondary';
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3 mb-2">
                        <div class="home-card" role="button" style="--card-cor: {{ $corHex[$cor] }}; cursor:pointer;"
                             data-toggle="modal" data-target="#freq-modal-{{ $matricula->id }}-{{ $loop->index }}">
                            <span class="freq-ring" style="--pct: {{ $linha['percentual'] ?? 0 }};">
                                <span class="freq-ring-valor">
                                    @if($linha['percentual'] !== null)
                                        {{ $linha['percentual'] }}%
                                    @else
                                        <i class="fas fa-book-open"></i>
                                    @endif
                                </span>
                            </span>
                            <span class="home-card-texto">
                                <span class="home-card-nome">{{ $linha['disciplina'] }}</span>
                                <p class="home-card-desc">
                                    {{ $linha['total'] }} {{ $linha['total'] === 1 ? 'aula' : 'aulas' }}
                                    · {{ $linha['faltas'] }} {{ $linha['faltas'] === 1 ? 'falta' : 'faltas' }}
                                    @if($linha['justificadas'] > 0)
                                        · {{ $linha['justificadas'] }} just.
                                    @endif
                                </p>
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-4">
                            <i class="far fa-calendar-check fa-2x mb-2 d-block"></i>
                            Nenhuma chamada registrada ainda.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@foreach($disciplinas as $linha)
    @php
        $cor = $linha['percentual'] !== null ? $corFrequencia($linha['percentual']) : 'secondary';
        $corModal = $corHex[$cor];
        $abonadas = $linha['registros']->where('status', 'abonada')->count();
        $segmentos = [
            ['valor' => $linha['presencas'], 'cor' => $corHex['success'], 'rotulo' => 'Presenças'],
            ['valor' => $linha['faltas'], 'cor' => $corHex['danger'], 'rotulo' => 'Faltas'],
            ['valor' => $linha['justificadas'], 'cor' => $corHex['warning'], 'rotulo' => 'Faltas justificadas'],
            ['valor' => $abonadas, 'cor' => $corHex['info'], 'rotulo' => 'Faltas abonadas'],
        ];
    @endphp
    <div class="modal fade freq-modal" id="freq-modal-{{ $matricula->id }}-{{ $loop->index }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content freq-modal-content">
                <div class="freq-modal-header" style="background: {{ $corSoft[$cor] }};">
                    <span class="freq-modal-icone" style="background: {{ $corModal }};">
                        <i class="fas fa-book-open"></i>
                    </span>
                    <div class="freq-modal-titulo-wrap">
                        <h5 class="freq-modal-titulo">{{ $linha['disciplina'] }}</h5>
                        <span class="freq-modal-sub">
                            {{ $linha['total'] }} {{ $linha['total'] === 1 ? 'aula registrada' : 'aulas registradas' }}
                        </span>
                    </div>
                    <button type="button" class="freq-modal-close" data-dismiss="modal" aria-label="Fechar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="freq-modal-resumo">
                    <div class="freq-gauge" style="--pct: {{ $linha['percentual'] ?? 0 }}; --mc: {{ $corModal }};">
                        <span class="freq-gauge-inner">
                            <span class="freq-gauge-valor">
                                {{ $linha['percentual'] !== null ? $linha['percentual'].'%' : '—' }}
                            </span>
                            <span class="freq-gauge-label">presença</span>
                        </span>
                    </div>
                    <div class="freq-stats">
                        <div class="freq-stat">
                            <span class="freq-stat-icone" style="background: {{ $corHex['success'] }};"><i class="fas fa-check"></i></span>
                            <span class="freq-stat-texto"><b>{{ $linha['presencas'] }}</b><span>{{ $linha['presencas'] === 1 ? 'presença' : 'presenças' }}</span></span>
                        </div>
                        <div class="freq-stat">
                            <span class="freq-stat-icone" style="background: {{ $corHex['danger'] }};"><i class="fas fa-times"></i></span>
                            <span class="freq-stat-texto"><b>{{ $linha['faltas'] }}</b><span>{{ $linha['faltas'] === 1 ? 'falta' : 'faltas' }}</span></span>
                        </div>
                        <div class="freq-stat">
                            <span class="freq-stat-icone" style="background: {{ $corHex['warning'] }};"><i class="fas fa-file-alt"></i></span>
                            <span class="freq-stat-texto"><b>{{ $linha['justificadas'] }}</b><span>{{ $linha['justificadas'] === 1 ? 'falta justificada' : 'faltas justificadas' }}</span></span>
                        </div>
                        <div class="freq-stat">
                            <span class="freq-stat-icone" style="background: {{ $corHex['info'] }};"><i class="fas fa-star"></i></span>
                            <span class="freq-stat-texto"><b>{{ $abonadas }}</b><span>{{ $abonadas === 1 ? 'falta abonada' : 'faltas abonadas' }}</span></span>
                        </div>
                    </div>
                </div>

                <div class="freq-modal-barra-wrap">
                    <div class="freq-barra">
                        @foreach($segmentos as $seg)
                            @if($seg['valor'] > 0)
                                <span class="freq-barra-seg" style="width: {{ round($seg['valor'] / $linha['total'] * 100, 2) }}%; background: {{ $seg['cor'] }};"></span>
                            @endif
                        @endforeach
                    </div>
                    <div class="freq-barra-legenda">
                        @foreach($segmentos as $seg)
                            @if($seg['valor'] > 0)
                                <span class="freq-legenda-item"><i style="background: {{ $seg['cor'] }};"></i>{{ $seg['rotulo'] }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="freq-lista-titulo">
                    <i class="far fa-calendar-alt mr-1"></i> Histórico de aulas
                </div>
                <div class="freq-lista">
                    @foreach($linha['registros'] as $reg)
                        @php
                            $data = \Carbon\Carbon::parse($reg->data);
                            $corReg = $corStatus[$reg->status] ?? 'secondary';
                        @endphp
                        <div class="freq-item">
                            <span class="freq-data">
                                <b>{{ $data->format('d') }}</b>
                                <span>{{ $mesesAbrev[$data->month] }}/{{ $data->format('y') }}</span>
                            </span>
                            <span class="freq-item-meio">
                                <span class="freq-item-dia">{{ $diasSemana[$data->dayOfWeek] }}</span>
                                @if($reg->justificativa)
                                    <span class="freq-item-just" title="{{ $reg->justificativa }}">{{ $reg->justificativa }}</span>
                                @endif
                            </span>
                            <span class="freq-pill" style="color: {{ $corHex[$corReg] }}; background: {{ $corSoft[$corReg] }};">
                                <i class="fas {{ $iconeStatus[$reg->status] ?? 'fa-circle' }}"></i>
                                {{ \App\Models\Frequencia::STATUS[$reg->status] ?? $reg->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endforeach

@once
@include('partials.home-cards-styles')
<style>
    .freq-ring {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        padding: 4px;
        flex-shrink: 0;
        background: conic-gradient(var(--card-cor, #6c757d) calc(var(--pct, 0) * 1%), #e9ecef 0);
    }
    .freq-ring-valor {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--card-cor, #6c757d);
    }
    .min-w-0 { min-width: 0; }

    .freq-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        font-family: 'Plus Jakarta Sans', sans-serif;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
    }
    .freq-modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 18px;
    }
    .freq-modal-icone {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .16);
    }
    .freq-modal-titulo-wrap { min-width: 0; flex: 1; }
    .freq-modal-titulo {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .freq-modal-sub { font-size: 12px; font-weight: 600; color: #64748b; }
    .freq-modal-close {
        border: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(15, 23, 42, .07);
        color: #475569;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background .15s ease;
    }
    .freq-modal-close:hover { background: rgba(15, 23, 42, .14); }

    .freq-modal-resumo {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 18px 18px 12px;
    }
    .freq-gauge {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        padding: 7px;
        flex-shrink: 0;
        background: conic-gradient(var(--mc, #6c757d) calc(var(--pct, 0) * 1%), #e9edf2 0);
    }
    .freq-gauge-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .freq-gauge-valor {
        font-size: 20px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -.02em;
        color: var(--mc, #6c757d);
    }
    .freq-gauge-label {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
        margin-top: 3px;
    }
    .freq-stats {
        flex: 1;
        min-width: 0;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .freq-stat {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 10px;
        background: #f8fafc;
        border: 1px solid #eef1f5;
        border-radius: 10px;
    }
    .freq-stat-icone {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 11px;
        flex-shrink: 0;
    }
    .freq-stat-texto { display: flex; flex-direction: column; line-height: 1.15; min-width: 0; }
    .freq-stat-texto b { font-size: 15px; font-weight: 800; color: #0f172a; }
    .freq-stat-texto span { font-size: 10px; font-weight: 600; color: #64748b; }

    .freq-modal-barra-wrap { padding: 4px 18px 12px; }
    .freq-barra {
        display: flex;
        height: 8px;
        border-radius: 99px;
        overflow: hidden;
        background: #e9edf2;
    }
    .freq-barra-seg { height: 100%; }
    .freq-barra-legenda {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 14px;
        margin-top: 8px;
    }
    .freq-legenda-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
    }
    .freq-legenda-item i {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .freq-lista-titulo {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        border-top: 1px solid #eef1f5;
        background: #f8fafc;
    }
    .freq-lista {
        max-height: 44vh;
        overflow-y: auto;
        padding: 6px 10px 10px;
    }
    .freq-lista::-webkit-scrollbar { width: 8px; }
    .freq-lista::-webkit-scrollbar-thumb { background: #d6dde6; border-radius: 99px; }
    .freq-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 8px;
        border-radius: 10px;
    }
    .freq-item:hover { background: #f1f5f9; }
    .freq-item + .freq-item { border-top: 1px solid #f1f5f9; }
    .freq-data {
        width: 46px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 5px 0;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        line-height: 1.1;
    }
    .freq-data b { font-size: 15px; font-weight: 800; color: #0f172a; }
    .freq-data span {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
    }
    .freq-item-meio { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .freq-item-dia { font-size: 12.5px; font-weight: 700; color: #334155; text-transform: capitalize; }
    .freq-item-just {
        font-size: 11px;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .freq-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .freq-pill i { font-size: 9px; }

    @media (max-width: 575.98px) {
        .freq-ring { width: 38px; height: 38px; padding: 3px; }
        .freq-ring-valor { font-size: 9px; }
        .freq-modal-resumo { flex-direction: column; gap: 12px; }
        .freq-stats { width: 100%; }
        .freq-lista { max-height: 38vh; }
        .freq-item-dia { font-size: 11.5px; }
        .freq-pill { font-size: 10px; padding: 3px 8px; }
    }
</style>
@endonce

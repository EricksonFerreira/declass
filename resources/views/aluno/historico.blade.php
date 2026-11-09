@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$aluno->nome ?? 'Aluno'" subtitle="Histórico completo — registro cumulativo" />
@stop

@push('css')
<style>
    .ah-perfil { text-align:center; padding-top:.4rem; }
    .ah-avatar { width:74px; height:74px; border-radius:50%; margin:0 auto .6rem; display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#2563eb,#7c3aed); }
    .ah-ra { font-size:.72rem; color:#94a3b8; letter-spacing:.06em; text-transform:uppercase; }
    .ah-stats { display:flex; gap:.4rem; justify-content:center; flex-wrap:wrap; margin-top:.6rem; }
    .ah-stat { background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px; padding:.3rem .6rem; font-size:.74rem; }
    .ah-tabs { border-bottom:2px solid #e5e7eb; }
    .ah-tabs .nav-link { border:0; font-size:.82rem; font-weight:700; color:#6b7280; padding:.55rem .9rem; }
    .ah-tabs .nav-link.active { color:#2563eb; border-bottom:2px solid #2563eb; margin-bottom:-2px; }
    .ah-pane-head { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin-bottom:.7rem; }
</style>
@endpush

@section('content')
@php
    $p = $aluno->pessoa;
    $fmtBrl = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $statusMatricula = \App\Models\Matricula::STATUS;
    $statusBadge = ['ativa' => 'success', 'trancada' => 'warning', 'cancelada' => 'danger', 'concluida' => 'info'];
    $iniciais = collect(explode(' ', trim($p->nome ?? $aluno->nome ?? '?')))->filter()->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->take(2)->implode('');
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body ah-perfil">
                <div class="ah-avatar">{{ $iniciais }}</div>
                <h5 class="font-weight-bold mb-0">{{ $p->nome ?? '—' }}</h5>
                <div class="ah-ra">RA {{ $aluno->registro_academico ?? '—' }}</div>
                <div class="ah-stats">
                    <span class="ah-stat"><i class="fa fa-school text-primary mr-1"></i>{{ $aluno->escola->nome ?? '—' }}</span>
                    <span class="ah-stat"><i class="fa fa-calendar text-primary mr-1"></i>{{ $anosNaEscola }} ano(s)</span>
                    @if($frequenciaGeral !== null)
                        <span class="ah-stat"><i class="fa fa-clipboard-check text-primary mr-1"></i>
                            <span class="badge badge-{{ $frequenciaGeral >= 75 ? 'success' : ($frequenciaGeral >= 50 ? 'warning' : 'danger') }}">{{ $frequenciaGeral }}%</span>
                        </span>
                    @endif
                    <span class="ah-stat"><i class="fa fa-wallet text-primary mr-1"></i>
                        @if($financeiro['valor_em_aberto'] > 0)
                            <span class="badge badge-danger">{{ $fmtBrl($financeiro['valor_em_aberto']) }}</span>
                        @else
                            <span class="text-success font-weight-bold">Em dia</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body pt-0">
                <dl class="row mb-0 small">
                    <dt class="col-sm-5">Nascimento</dt><dd class="col-sm-7">{{ optional($p->data_nascimento)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Ingresso</dt><dd class="col-sm-7">{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Status</dt><dd class="col-sm-7"><span class="badge badge-{{ $aluno->ativo === 'S' ? 'success' : 'secondary' }}">{{ $aluno->ativo === 'S' ? 'Ativo' : 'Inativo' }}</span></dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('aluno.show', $aluno->id) }}" class="btn btn-secondary btn-sm">Cadastro</a>
                <a href="{{ route('aluno.historico.escolar.pdf', $aluno->id) }}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-graduation-cap"></i> Histórico (PDF)</a>
                <a href="{{ route('aluno.historico.pdf', $aluno->id) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-file-pdf"></i> Exportar PDF</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <ul class="nav nav-tabs ah-tabs px-2 pt-2" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#ah-escolar"><i class="fa fa-graduation-cap mr-1"></i> Escolar</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ah-fin"><i class="fa fa-wallet mr-1"></i> Financeiro</a></li>
                @if($saude)
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ah-saude"><i class="fa fa-notes-medical mr-1"></i> Saúde</a></li>
                @endif
                @if($transporte)
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ah-transp"><i class="fa fa-bus mr-1"></i> Transporte</a></li>
                @endif
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ah-tempo"><i class="fa fa-stream mr-1"></i> Linha do tempo <span class="badge badge-light border ml-1">{{ $eventos->count() }}</span></a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="ah-escolar">
                    <div class="card-body pt-3">
                        <div class="ah-pane-head">Histórico escolar por ano letivo ({{ $matriculas->count() }})</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Ano letivo</th><th>Turma</th><th>Etapa</th><th>Situação</th>
                                        <th>Chamada</th><th>Ingresso</th><th>Frequência</th><th>Média</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($matriculas as $m)
                                        @php
                                            $freq = $frequencias[$m->id] ?? null;
                                            $pres = $freq ? (int) ($freq['presente'] ?? 0) : 0;
                                            $freqTotal = $freq ? (int) $freq->sum() : 0;
                                            $freqPct = $freqTotal > 0 ? round($pres / $freqTotal * 100) : null;
                                            $faltas = $freq ? (int) (($freq['falta'] ?? 0) + ($freq['justificada'] ?? 0)) : 0;
                                            $media = $medias[$m->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td class="font-weight-bold">{{ $m->turma->anoLetivo->ano ?? '—' }}</td>
                                            <td>{{ $m->turma->nome ?? '—' }}</td>
                                            <td>{{ $m->turma->etapa->nome ?? '—' }}</td>
                                            <td><span class="badge badge-{{ $statusBadge[$m->status] ?? 'secondary' }}">{{ $statusMatricula[$m->status] ?? $m->status }}</span></td>
                                            <td>{{ $m->numero_chamada ?? '—' }}</td>
                                            <td>{{ optional($m->data_matricula)->format('d/m/Y') ?? '—' }}</td>
                                            <td>
                                                @if($freqPct !== null)
                                                    <span class="badge badge-{{ $freqPct >= 75 ? 'success' : ($freqPct >= 50 ? 'warning' : 'danger') }}" title="{{ $pres }} presenças · {{ $faltas }} faltas">{{ $freqPct }}%</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($media)
                                                    <span class="badge badge-info" title="{{ $media['total'] }} nota(s)">{{ $media['media'] }}%</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('aluno.historico.escolar', [$aluno->id, $m->id]) }}" class="btn btn-outline-primary btn-sm" title="Ver grade de notas do ano">
                                                    <i class="fa fa-list-alt"></i> Ano
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-graduation-cap fa-2x mb-2 d-block"></i>
                                                Nenhuma matrícula registrada para este aluno.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="ah-fin">
                    <div class="card-body pt-3">
                        <div class="ah-pane-head">Mensalidades</div>
                        @if($financeiro['total'] === 0)
                            <p class="text-muted mb-0">Nenhuma mensalidade registrada.</p>
                        @else
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Pagas</dt><dd class="col-sm-3 text-success">{{ $financeiro['pagas'] }}</dd>
                                <dt class="col-sm-3">Pendentes</dt><dd class="col-sm-3">{{ $financeiro['pendentes'] }}</dd>
                                <dt class="col-sm-3">A vencer</dt><dd class="col-sm-3">{{ $financeiro['a_vencer'] }}</dd>
                                <dt class="col-sm-3">Atrasadas</dt>
                                <dd class="col-sm-3">
                                    @if($financeiro['atrasadas'] > 0)
                                        <span class="badge badge-danger">{{ $financeiro['atrasadas'] }} · {{ $fmtBrl($financeiro['valor_atrasado']) }}</span>
                                    @else
                                        0
                                    @endif
                                </dd>
                                <dt class="col-sm-3">Em aberto</dt><dd class="col-sm-3 font-weight-bold">{{ $fmtBrl($financeiro['valor_em_aberto']) }}</dd>
                            </dl>
                        @endif
                    </div>
                </div>

                @if($saude)
                    <div class="tab-pane fade" id="ah-saude">
                        <div class="card-body pt-3">
                            <div class="ah-pane-head text-danger"><i class="fa fa-notes-medical mr-1"></i> Informações de saúde</div>
                            <dl class="row mb-0">
                                @if($saude->tipo_sanguineo)<dt class="col-sm-4">Tipo sanguíneo</dt><dd class="col-sm-8">{{ \App\Models\SaudeAluno::TIPOS_SANGUINEOS[$saude->tipo_sanguineo] ?? $saude->tipo_sanguineo }}</dd>@endif
                                @if($saude->alergias)<dt class="col-sm-4">Alergias</dt><dd class="col-sm-8">{{ $saude->alergias }}</dd>@endif
                                @if($saude->medicamentos)<dt class="col-sm-4">Medicamentos</dt><dd class="col-sm-8">{{ $saude->medicamentos }}</dd>@endif
                                @if($saude->condicoes_cronicas)<dt class="col-sm-4">Condições crônicas</dt><dd class="col-sm-8">{{ $saude->condicoes_cronicas }}</dd>@endif
                                @if($saude->contato_emergencia_nome)<dt class="col-sm-4">Contato de emergência</dt><dd class="col-sm-8">{{ $saude->contato_emergencia_nome }} ({{ $saude->contato_emergencia_telefone ?? '—' }})</dd>@endif
                            </dl>
                        </div>
                    </div>
                @endif

                @if($transporte)
                    <div class="tab-pane fade" id="ah-transp">
                        <div class="card-body pt-3">
                            <div class="ah-pane-head"><i class="fa fa-bus mr-1"></i> Transporte escolar</div>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Rota</dt><dd class="col-sm-8">{{ $transporte->rota->nome ?? '—' }}</dd>
                                <dt class="col-sm-4">Parada</dt><dd class="col-sm-8">{{ $transporte->parada->nome ?? '—' }}</dd>
                                @if($transporte->rota?->horario_ida || $transporte->rota?->horario_volta)
                                    <dt class="col-sm-4">Horários</dt>
                                    <dd class="col-sm-8">
                                        Ida {{ optional($transporte->rota->horario_ida)->format('H:i') ?? '—' }} ·
                                        Volta {{ optional($transporte->rota->horario_volta)->format('H:i') ?? '—' }}
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

                <div class="tab-pane fade" id="ah-tempo">
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($eventos as $evento)
                                <li class="list-group-item d-flex align-items-start">
                                    <span class="badge badge-{{ $evento['cor'] }} mr-3 text-nowrap" style="min-width: 90px;">
                                        {{ \Illuminate\Support\Carbon::parse($evento['data'])->format('d/m/Y') }}
                                    </span>
                                    <div>
                                        <span class="badge badge-light border mr-1">{{ $evento['tipo'] }}</span>
                                        <strong>{{ $evento['titulo'] }}</strong>
                                        @if($evento['detalhe'])
                                            <div class="text-muted small">{{ $evento['detalhe'] }}</div>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">
                                    <i class="fas fa-stream fa-2x mb-2 d-block"></i>
                                    Nenhum evento registrado ainda.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    @if($eventos->count() >= 40)
                        <div class="card-footer text-muted small">Exibindo os 40 eventos mais recentes.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

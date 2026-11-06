@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$aluno->nome ?? 'Aluno'" subtitle="Histórico completo — registro cumulativo" />
@stop

@section('content')
@php
    $p = $aluno->pessoa;
    $fmtBrl = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $statusMatricula = \App\Models\Matricula::STATUS;
    $statusBadge = ['ativa' => 'success', 'trancada' => 'warning', 'cancelada' => 'danger', 'concluida' => 'info'];
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Resumo</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nome</dt><dd class="col-sm-7">{{ $p->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Nascimento</dt><dd class="col-sm-7">{{ optional($p->data_nascimento)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">RA</dt><dd class="col-sm-7">{{ $aluno->registro_academico ?? '—' }}</dd>
                    <dt class="col-sm-5">Escola</dt><dd class="col-sm-7">{{ $aluno->escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Ingresso</dt><dd class="col-sm-7">{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Anos na escola</dt><dd class="col-sm-7 font-weight-bold">{{ $anosNaEscola }}</dd>
                    <dt class="col-sm-5">Frequência geral</dt>
                    <dd class="col-sm-7">
                        @if($frequenciaGeral !== null)
                            <span class="badge badge-{{ $frequenciaGeral >= 75 ? 'success' : ($frequenciaGeral >= 50 ? 'warning' : 'danger') }}">{{ $frequenciaGeral }}%</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>
                    <dt class="col-sm-5">Em aberto</dt>
                    <dd class="col-sm-7">
                        @if($financeiro['valor_em_aberto'] > 0)
                            <span class="badge badge-danger">{{ $fmtBrl($financeiro['valor_em_aberto']) }}</span>
                        @else
                            <span class="text-success">Em dia</span>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('aluno.show', $aluno->id) }}" class="btn btn-secondary btn-sm">Cadastro</a>
                <a href="{{ route('aluno.historico.pdf', $aluno->id) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-file-pdf"></i> Exportar PDF</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Financeiro</div>
            <div class="card-body">
                @if($financeiro['total'] === 0)
                    <p class="text-muted mb-0">Nenhuma mensalidade registrada.</p>
                @else
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Pagas</dt><dd class="col-sm-6 text-success">{{ $financeiro['pagas'] }}</dd>
                        <dt class="col-sm-6">Pendentes</dt><dd class="col-sm-6">{{ $financeiro['pendentes'] }}</dd>
                        <dt class="col-sm-6">A vencer</dt><dd class="col-sm-6">{{ $financeiro['a_vencer'] }}</dd>
                        <dt class="col-sm-6">Atrasadas</dt>
                        <dd class="col-sm-6">
                            @if($financeiro['atrasadas'] > 0)
                                <span class="badge badge-danger">{{ $financeiro['atrasadas'] }} · {{ $fmtBrl($financeiro['valor_atrasado']) }}</span>
                            @else
                                0
                            @endif
                        </dd>
                        <dt class="col-sm-6">Em aberto</dt><dd class="col-sm-6 font-weight-bold">{{ $fmtBrl($financeiro['valor_em_aberto']) }}</dd>
                    </dl>
                @endif
            </div>
        </div>

        @if($saude)
            <div class="card border-danger">
                <div class="card-header font-weight-bold text-danger"><i class="fa fa-notes-medical"></i> Saúde</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        @if($saude->tipo_sanguineo)<dt class="col-sm-5">Sangue</dt><dd class="col-sm-7">{{ \App\Models\SaudeAluno::TIPOS_SANGUINEOS[$saude->tipo_sanguineo] ?? $saude->tipo_sanguineo }}</dd>@endif
                        @if($saude->alergias)<dt class="col-sm-5">Alergias</dt><dd class="col-sm-7">{{ $saude->alergias }}</dd>@endif
                        @if($saude->medicamentos)<dt class="col-sm-5">Medicamentos</dt><dd class="col-sm-7">{{ $saude->medicamentos }}</dd>@endif
                        @if($saude->condicoes_cronicas)<dt class="col-sm-5">Condições</dt><dd class="col-sm-7">{{ $saude->condicoes_cronicas }}</dd>@endif
                        @if($saude->contato_emergencia_nome)<dt class="col-sm-5">Emergência</dt><dd class="col-sm-7">{{ $saude->contato_emergencia_nome }} ({{ $saude->contato_emergencia_telefone ?? '—' }})</dd>@endif
                    </dl>
                </div>
            </div>
        @endif

        @if($transporte)
            <div class="card">
                <div class="card-header font-weight-bold"><i class="fa fa-bus"></i> Transporte</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Rota</dt><dd class="col-sm-7">{{ $transporte->rota->nome ?? '—' }}</dd>
                        <dt class="col-sm-5">Parada</dt><dd class="col-sm-7">{{ $transporte->parada->nome ?? '—' }}</dd>
                        @if($transporte->rota?->horario_ida || $transporte->rota?->horario_volta)
                            <dt class="col-sm-5">Horários</dt>
                            <dd class="col-sm-7">
                                Ida {{ optional($transporte->rota->horario_ida)->format('H:i') ?? '—' }} ·
                                Volta {{ optional($transporte->rota->horario_volta)->format('H:i') ?? '—' }}
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">Histórico escolar por ano letivo ({{ $matriculas->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ano letivo</th>
                            <th>Turma</th>
                            <th>Etapa</th>
                            <th>Situação</th>
                            <th>Chamada</th>
                            <th>Ingresso</th>
                            <th>Frequência</th>
                            <th>Média</th>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-graduation-cap fa-2x mb-2 d-block"></i>
                                    Nenhuma matrícula registrada para este aluno.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Linha do tempo ({{ $eventos->count() }})</div>
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
@stop

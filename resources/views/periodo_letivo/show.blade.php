@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$periodo->nome" :subtitle="$periodo->anoLetivo->ano ?? ''" />
@stop

@section('content')
@php
    $anoLetivo = $periodo->anoLetivo;
    $podeGerar = auth()->user()->hasPermissao('turma.gerar_lote');
    $nomesExistentes = $turmas->pluck('nome')->map(fn ($n) => mb_strtolower($n))->all();
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Dados do Período</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Período</dt><dd class="col-sm-7">{{ $periodo->nome }}</dd>
                    <dt class="col-sm-5">Ordem</dt><dd class="col-sm-7">{{ $periodo->ordem }}º</dd>
                    <dt class="col-sm-5">Início</dt><dd class="col-sm-7">{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Fim</dt><dd class="col-sm-7">{{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Escola</dt><dd class="col-sm-7">{{ $periodo->escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7">
                        @if($periodo->estaAtivo())
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-secondary">Inativo</span>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('periodo_letivo.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('periodo_letivo.edit')
                    <a href="{{ route('periodo_letivo.edit', $periodo->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
            </div>
        </div>

        @if($anoLetivo)
            <div class="card">
                <div class="card-header font-weight-bold">Ano Letivo</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Ano</dt><dd class="col-sm-7 font-weight-bold">{{ $anoLetivo->ano }}</dd>
                        <dt class="col-sm-5">Períodos</dt><dd class="col-sm-7">{{ $anoLetivo->periodos->count() }}</dd>
                        <dt class="col-sm-5">Periodicidade</dt>
                        <dd class="col-sm-7">{{ \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">Turmas do ano letivo {{ $anoLetivo->ano ?? '' }} ({{ $turmas->count() }})</span>
                <div>
                    @if($podeGerar)
                        <a href="#gerador" class="btn btn-info btn-sm"><i class="fa fa-layer-group"></i> Geração em massa</a>
                        <a href="{{ route('turma.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nova turma</a>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Turma</th>
                            <th>Etapa</th>
                            <th>Turno</th>
                            <th>Sala</th>
                            <th>Matrículas</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turmas as $turma)
                            <tr>
                                <td class="font-weight-bold">{{ $turma->nome }}</td>
                                <td>{{ $turma->etapa->nome ?? '—' }}</td>
                                <td>{{ $turnosOpcoes[$turma->turno ?? ''] ?? '—' }}</td>
                                <td>{{ $turma->sala ?? '—' }}</td>
                                <td>{{ $turma->matriculas_count ?? '—' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('turma.show', $turma->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                                    @can('turma.edit')
                                        <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-chalkboard fa-2x mb-2 d-block"></i>
                                    Nenhuma turma neste ano letivo.
                                    @if($podeGerar) Use a <strong>geração em massa</strong> abaixo para criar todas de uma vez. @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($podeGerar && $anoLetivo)
            <div class="card" id="gerador">
                <div class="card-header font-weight-bold">
                    <i class="fa fa-layer-group"></i> Geração de turmas em massa
                    <small class="text-muted font-weight-normal">— crie todas as turmas do ano letivo {{ $anoLetivo->ano }} de uma vez</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('turma.gerar_lote') }}" id="form-gerar-lote">
                        @csrf
                        <input type="hidden" name="ano_letivo_id" value="{{ $anoLetivo->id }}">

                        <div class="row">
                            <div class="col-md-6">
                                <label class="font-weight-bold">Etapas <span class="text-danger">*</span></label>
                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                                    @foreach($etapasOpcoes as $etapa)
                                        <div class="form-check">
                                            <input class="form-check-input etapa-check" type="checkbox" name="etapas[]" value="{{ $etapa->id }}" id="etapa_{{ $etapa->id }}" checked>
                                            <label class="form-check-label" for="etapa_{{ $etapa->id }}">{{ $etapa->nome }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Marque as etapas que terão turmas.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold">Turnos <small class="text-muted">(nenhum = sem turno)</small></label>
                                <div class="border rounded p-2">
                                    @foreach($turnosOpcoes as $valor => $label)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input turno-check" type="checkbox" name="turnos[]" value="{{ $valor }}" id="turno_{{ $valor }}">
                                            <label class="form-check-label" for="turno_{{ $valor }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Com 2+ turnos, o nome ganha o sufixo do turno (ex.: "1º Ano A — Matutino").</small>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="font-weight-bold">Sufixos <span class="text-danger">*</span></label>
                                <input type="text" name="sufixos" id="input-sufixos" class="form-control" value="A, B, C, D" required>
                                <small class="text-muted">Separados por vírgula: letras (A, B, C) ou números (1, 2, 3).</small>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">Sala <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="sala" class="form-control" maxlength="40" placeholder="Ex.: 101">
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">Capacidade <small class="text-muted">(opcional)</small></label>
                                <input type="number" name="capacidade_maxima" class="form-control" min="1" max="500" placeholder="Ex.: 40">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="font-weight-bold">Pré-visualização</label>
                            <div id="preview-lote" class="border rounded p-2 bg-light" style="max-height: 160px; overflow-y: auto; font-size: .9rem;"></div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success" id="btn-gerar">
                                <i class="fa fa-check"></i> Criar turmas
                            </button>
                            <span class="text-muted ml-2">Turmas com nome já existente no ano serão puladas automaticamente.</span>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            (function () {
                var etapas = @json($etapasOpcoes->mapWithKeys(fn ($e) => [$e->id => $e->nome]));
                var turnos = @json($turnosOpcoes);
                var existentes = @json($nomesExistentes);
                var preview = document.getElementById('preview-lote');
                var btn = document.getElementById('btn-gerar');

                function nomes() {
                    var sufixos = document.getElementById('input-sufixos').value
                        .split(',').map(function (s) { return s.trim(); }).filter(Boolean);
                    var turnosSel = Array.prototype.slice.call(document.querySelectorAll('.turno-check:checked'))
                        .map(function (c) { return c.value; });
                    var etapasSel = Array.prototype.slice.call(document.querySelectorAll('.etapa-check:checked'))
                        .map(function (c) { return c.value; });
                    var rotuloTurno = turnosSel.length > 1;
                    var nomes = [];

                    etapasSel.forEach(function (eid) {
                        sufixos.forEach(function (suf) {
                            (turnosSel.length ? turnosSel : [null]).forEach(function (t) {
                                var nome = (etapas[eid] + ' ' + suf + (rotuloTurno && t ? ' — ' + turnos[t] : '')).trim();
                                nomes.push({ nome: nome, existe: existentes.indexOf(nome.toLowerCase()) !== -1 });
                            });
                        });
                    });
                    return nomes;
                }

                function atualizar() {
                    var lista = nomes();
                    var novos = lista.filter(function (n) { return !n.existe; });
                    var repetidos = lista.length - novos.length;

                    preview.innerHTML = lista.length === 0
                        ? '<span class="text-muted">Selecione etapas, turnos e sufixos para ver a pré-visualização.</span>'
                        : '<strong>' + novos.length + ' turma(s) serão criadas</strong>' + (repetidos ? ' · ' + repetidos + ' já existem (serão puladas)' : '') +
                          '<ul class="mb-0 mt-1 pl-3">' +
                          lista.slice(0, 60).map(function (n) {
                              return '<li' + (n.existe ? ' class="text-muted" style="text-decoration: line-through;"' : '') + '>' + n.nome + '</li>';
                          }).join('') +
                          (lista.length > 60 ? '<li class="text-muted">… e mais ' + (lista.length - 60) + '</li>' : '') +
                          '</ul>';
                    btn.disabled = novos.length === 0;
                }

                document.querySelectorAll('.etapa-check, .turno-check, #input-sufixos').forEach(function (el) {
                    el.addEventListener('change', atualizar);
                    el.addEventListener('input', atualizar);
                });
                atualizar();
            })();
            </script>
        @endif
    </div>
</div>
@stop

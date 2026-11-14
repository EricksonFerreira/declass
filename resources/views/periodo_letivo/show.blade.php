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
                        @if($periodo->esta_ativo)
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
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modal-gerar-lote"><i class="fa fa-layer-group"></i> Geração em massa</button>
                        <a href="{{ route('turma.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nova turma</a>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
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
                                    @if($podeGerar) Use a <strong>geração em massa</strong> acima para criar todas de uma vez. @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>

        @include('turma.partials.gerador-lote', [
            'anoLetivo' => $anoLetivo,
            'etapasOpcoes' => $etapasOpcoes,
            'turnosOpcoes' => $turnosOpcoes,
            'nomesExistentes' => $nomesExistentes,
            'podeGerar' => $podeGerar,
        ])
    </div>
</div>
@stop

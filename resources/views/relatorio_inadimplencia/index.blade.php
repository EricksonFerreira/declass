@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Cobranças por situação — dívidas mais antigas primeiro" />
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">A vencer</span>
                <span class="info-box-number">{{ $resumo['a_vencer'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Vencidas</span>
                <span class="info-box-number">{{ $resumo['vencidas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-secondary"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em aberto</span>
                <span class="info-box-number">R$ {{ number_format($resumo['valor_aberto'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor vencido</span>
                <span class="info-box-number text-danger">R$ {{ number_format($resumo['valor_vencido'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Escola</th>
                        <th>Turma</th>
                        <th>Aluno</th>
                        <th class="text-center">#</th>
                        <th>Competência</th>
                        <th>Vencimento</th>
                        <th class="text-right">Valor</th>
                        <th>Situação</th>
                        <th class="text-center">Atraso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mensalidades as $m)
                        @php
                            $cor = match($m->situacao) {
                                'vencida' => 'danger',
                                'a_vencer' => 'warning',
                                'paga' => 'success',
                                default => 'secondary',
                            };
                            $diasAtraso = $m->situacao === 'vencida' && $m->vencimento
                                ? \Carbon\Carbon::parse($m->vencimento)->diffInDays(now())
                                : 0;
                        @endphp
                        <tr>
                            <td><small>{{ $m->escola }}</small></td>
                            <td>{{ $m->turma }}</td>
                            <td class="font-weight-bold">{{ $m->aluno }}</td>
                            <td class="text-center">{{ $m->numero_chamada ?? '—' }}</td>
                            <td>{{ $m->competencia }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->vencimento)->format('d/m/Y') }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($m->valor, 2, ',', '.') }}</td>
                            <td><span class="badge badge-{{ $cor }}">{{ ucfirst(str_replace('_', ' ', $m->situacao)) }}</span></td>
                            <td class="text-center">
                                @if($m->situacao === 'vencida')
                                    <span class="text-danger font-weight-bold">{{ $diasAtraso }}d</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-money-bill-wave fa-2x mb-2 d-block"></i>
                                Nenhuma mensalidade encontrada com os filtros informados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($mensalidades->hasPages())
        <div class="card-footer">
            {{ $mensalidades->links() }}
        </div>
    @endif
</div>
@stop

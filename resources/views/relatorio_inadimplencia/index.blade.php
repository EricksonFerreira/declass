@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Cobranças por situação — dívidas mais antigas primeiro" />
@stop

@push('css')
<style>
    .rf-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .rf-stat { background: #fff; border: 1px solid #e8edf5; border-radius: 14px; padding: .85rem 1rem; display: flex; align-items: center; gap: .75rem; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .rf-stat i { width: 40px; height: 40px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
    .rf-stat .n { font-size: 1.3rem; font-weight: 800; color: #0f172a; line-height: 1.05; }
    .rf-stat .n--danger { color: #dc2626; }
    .rf-stat .l { font-size: .66rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }
    .rf-ic--blue { background: #eff6ff; color: #3b82f6; }
    .rf-ic--slate { background: #f1f5f9; color: #64748b; }
    .rf-ic--green { background: #f0fdf4; color: #22c55e; }
    .rf-ic--red { background: #fef2f2; color: #dc2626; }
    .rf-ic--amber { background: #fffbeb; color: #f59e0b; }

    .rf-card { background: #fff; border: 1px solid #e8edf5; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.05); margin-bottom: 1rem; overflow: hidden; }
    .rf-head { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.15rem; border-bottom: 1px solid #f1f5f9; background: #fbfcfe; }
    .rf-head > i { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .78rem; flex-shrink: 0; }
    .rf-head .rf-t { font-size: .86rem; font-weight: 800; color: #0f172a; }
    .rf-head .rf-s { font-size: .72rem; color: #94a3b8; }
    .rf-head .flex-grow-1 { min-width: 0; }
</style>
@endpush

@section('content')
{{-- Resumo --}}
<div class="rf-stats">
    <div class="rf-stat">
        <i class="fas fa-clock rf-ic--amber"></i>
        <div><div class="n">{{ $resumo['a_vencer'] }}</div><div class="l">A vencer</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-exclamation-circle rf-ic--red"></i>
        <div><div class="n">{{ $resumo['vencidas'] }}</div><div class="l">Vencidas</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-money-bill-wave rf-ic--slate"></i>
        <div><div class="n">R$ {{ number_format($resumo['valor_aberto'], 2, ',', '.') }}</div><div class="l">Em aberto</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-check-circle rf-ic--green"></i>
        <div><div class="n n--danger">R$ {{ number_format($resumo['valor_vencido'], 2, ',', '.') }}</div><div class="l">Valor vencido</div></div>
    </div>
</div>

<div class="rf-card">
    <div class="rf-head">
        <i class="fas fa-money-bill-wave rf-ic--red"></i>
        <div class="flex-grow-1">
            <div class="rf-t">Mensalidades</div>
            <div class="rf-s">Cobranças por situação — dívidas mais antigas primeiro</div>
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>
    <div class="px-3 pt-3">
        <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th class="pl-3">Escola</th>
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
                        <td class="pl-3"><small>{{ $m->escola }}</small></td>
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
    @if($mensalidades->hasPages())
        <div class="px-3 py-2 border-top">
            {{ $mensalidades->links() }}
        </div>
    @endif
</div>
@stop

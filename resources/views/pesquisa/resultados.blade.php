@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Resultados: '.$pesquisa->titulo" />
@stop

@push('css')
<style>
    .rs-head { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.9rem 1.1rem; }
    .rs-head h5 { margin:0; font-weight:700; }
    .rs-chip { display:inline-flex; align-items:center; gap:.3rem; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.25); color:#fff; border-radius:999px; padding:.15rem .6rem; font-size:.74rem; margin:.15rem .25rem .15rem 0; }
    .rs-count { border:1px solid #eef1f6; border-radius:8px; padding:.35rem .7rem; background:#fff; font-size:.8rem; color:#374151; }
    .rs-count b { display:block; font-size:1.05rem; color:#1e3a8a; }
    .rs-count label { font-size:.62rem; text-transform:uppercase; color:#9ca3af; margin:0; font-weight:700; }
    .rs-num { width:30px; height:30px; border-radius:50%; background:#2563eb; color:#fff; font-weight:700; font-size:.8rem; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .rs-bar { height:14px; border-radius:7px; background:#e5e7eb; overflow:hidden; }
    .rs-bar > div { height:100%; }
    .rs-split { display:flex; height:22px; border-radius:6px; overflow:hidden; font-size:.7rem; color:#fff; font-weight:700; }
    .rs-split > div { display:flex; align-items:center; justify-content:center; }
    .rs-quote { border-left:3px solid #93c5fd; background:#f8fafc; border-radius:0 8px 8px 0; padding:.4rem .7rem; margin-bottom:.4rem; font-size:.84rem; }
    .rs-score { font-size:2rem; font-weight:800; color:#1e3a8a; line-height:1; }
    .rs-stars { color:#f59e0b; font-size:.95rem; }
    .rs-pergunta { border:1px solid #eef1f6; border-radius:10px; padding:.9rem 1rem; margin-bottom:.9rem; background:#fff; }
</style>
@endpush

@section('content')
@php $statusCores = ['rascunho' => 'secondary', 'aberta' => 'success', 'encerrada' => 'dark']; @endphp
<div class="card">
    <div class="rs-head d-flex flex-wrap align-items-center">
        <div class="flex-grow-1">
            <h5>{{ $pesquisa->titulo }}</h5>
            <div class="mt-1">
                <span class="rs-chip"><i class="fa fa-tag"></i> {{ $pesquisa->status_label }}</span>
                <span class="rs-chip"><i class="fa fa-calendar"></i> {{ $pesquisa->data_inicio->format('d/m/Y') }} a {{ $pesquisa->data_fim->format('d/m/Y') }}</span>
                <span class="rs-chip"><i class="fa fa-school"></i> {{ $pesquisa->escola->nome ?? 'Rede toda' }}</span>
            </div>
        </div>
        <div class="d-flex" style="gap:.4rem;">
            <div class="rs-count"><b>{{ $resultados['total_respostas'] }}</b><label>Respostas</label></div>
            <div class="rs-count"><b>{{ count($resultados['por_pergunta']) }}</b><label>Perguntas</label></div>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('pesquisa.resultados', $pesquisa->id) }}" class="d-flex flex-wrap align-items-end mb-3 p-2" style="gap:.5rem; background:#f8fafc; border:1px solid #eef1f6; border-radius:8px;">
            <div>
                <label class="small text-muted mb-0">Respondidas de</label>
                <input type="date" name="de" value="{{ $filtros['de'] ?? '' }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="small text-muted mb-0">até</label>
                <input type="date" name="ate" value="{{ $filtros['ate'] ?? '' }}" class="form-control form-control-sm">
            </div>
            <div class="flex-grow-1">
                <label class="small text-muted mb-0">Pergunta</label>
                <select name="pergunta_id" class="form-control form-control-sm">
                    <option value="">Todas as perguntas</option>
                    @foreach($pesquisa->perguntas as $pg)
                        <option value="{{ $pg->id }}" {{ (string) ($filtros['pergunta_id'] ?? '') === (string) $pg->id ? 'selected' : '' }}>{{ $loop->iteration }}. {{ \Illuminate\Support\Str::limit($pg->pergunta, 60) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                <a href="{{ route('pesquisa.resultados', $pesquisa->id) }}" class="btn btn-sm btn-secondary">Limpar</a>
            </div>
        </form>

        @forelse($resultados['por_pergunta'] as $item)
            @php $p = $item['pergunta']; @endphp
            <div class="rs-pergunta">
                <div class="d-flex align-items-start mb-2">
                    <span class="rs-num mr-2">{{ $loop->iteration }}</span>
                    <div class="flex-grow-1">
                        <strong>{{ $p->pergunta }}</strong>
                        <span class="badge badge-light border ml-1">{{ $p->tipo_label }}</span>
                        <small class="text-muted">· {{ $item['total'] }} resposta(s)</small>
                    </div>
                </div>

                @if($p->tipo === 'nota')
                    <div class="d-flex align-items-center flex-wrap" style="gap:1.2rem;">
                        <div class="text-center" style="min-width:80px;">
                            <div class="rs-score">{{ $item['media'] !== null ? number_format($item['media'], 1, ',', '') : '—' }}</div>
                            <div class="rs-stars">
                                @if($item['media'] !== null)
                                    @for($s = 1; $s <= 5; $s++)
                                        <i class="fa{{ $item['media'] >= $s - 0.5 ? 's' : 'r' }} fa-star"></i>
                                    @endfor
                                @endif
                            </div>
                            <small class="text-muted">média</small>
                        </div>
                        <div class="flex-grow-1">
                            @foreach([5,4,3,2,1] as $nota)
                                @php $qtd = $item['distribuicao'][$nota] ?? 0; @endphp
                                @php $pct = $item['total'] > 0 ? round($qtd / $item['total'] * 100) : 0; @endphp
                                <div class="d-flex align-items-center small mb-1">
                                    <span class="text-nowrap" style="width:52px;">
                                        <i class="fas fa-star text-warning mr-1"></i>{{ $nota }}
                                    </span>
                                    <div class="rs-bar flex-grow-1 mx-2">
                                        <div style="width:{{ $pct }}%; background:{{ $nota >= 4 ? '#22c55e' : ($nota === 3 ? '#f59e0b' : '#ef4444') }};"></div>
                                    </div>
                                    <span class="text-muted" style="width:80px;">{{ $qtd }} ({{ $pct }}%)</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($p->tipo === 'sim_nao')
                    @php
                        $tot = ($item['sim'] + $item['nao']) ?: 1;
                        $pctSim = round($item['sim'] / $tot * 100);
                        $pctNao = round($item['nao'] / $tot * 100);
                    @endphp
                    <div class="rs-split">
                        <div style="width:{{ max($pctSim, 12) }}%; background:#22c55e;">Sim · {{ $item['sim'] }} ({{ $pctSim }}%)</div>
                        <div style="width:{{ max($pctNao, 12) }}%; background:#ef4444;">Não · {{ $item['nao'] }} ({{ $pctNao }}%)</div>
                    </div>
                @else
                    @forelse($item['textos'] as $t)
                        <div class="rs-quote">
                            <small class="text-muted d-block mb-1"><i class="fa fa-user-circle mr-1"></i>{{ $t['usuario'] ?? 'Usuário' }}</small>
                            {{ $t['texto'] }}
                        </div>
                    @empty
                        <small class="text-muted">Nenhum texto livre respondido.</small>
                    @endforelse
                @endif
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-clipboard-question fa-3x mb-2 d-block"></i>
                Esta pesquisa ainda não tem perguntas.
            </div>
        @endforelse
    </div>

    <div class="card-footer">
        <a href="{{ route('pesquisa.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
        @can('pesquisa.edit')
            <a href="{{ route('pesquisa.edit', $pesquisa->id) }}" class="btn btn-warning btn-sm">Editar pesquisa</a>
        @endcan
    </div>
</div>
@stop

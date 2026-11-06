@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Resultados: '.$pesquisa->titulo" />
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center" style="gap:.75rem;">
            <div>
                <span class="badge badge-{{ ['rascunho' => 'secondary', 'aberta' => 'success', 'encerrada' => 'dark'][$pesquisa->status] ?? 'secondary' }}">{{ $pesquisa->statusLabel() }}</span>
                <span class="text-muted ml-2">
                    {{ $pesquisa->data_inicio->format('d/m/Y') }} a {{ $pesquisa->data_fim->format('d/m/Y') }}
                    · {{ $pesquisa->escola->nome ?? 'Rede toda' }}
                </span>
            </div>
            <div class="ml-auto">
                <strong>{{ $resultados['total_respostas'] }}</strong> resposta(s) recebida(s)
            </div>
        </div>
    </div>

    <div class="card-body">
        @forelse($resultados['por_pergunta'] as $item)
            @php($p = $item['pergunta'])
            <div class="border rounded p-3 mb-3">
                <h6 class="font-weight-bold mb-1">
                    {{ $loop->iteration }}. {{ $p->pergunta }}
                    <span class="badge badge-light border ml-1">{{ $p->tipoLabel() }}</span>
                    <small class="text-muted font-weight-normal">({{ $item['total'] }} resposta(s))</small>
                </h6>

                @if($p->tipo === 'nota')
                    <div class="d-flex align-items-center flex-wrap" style="gap:1rem;">
                        <div class="h4 mb-0 text-primary font-weight-bold">{{ $item['media'] !== null ? number_format($item['media'], 1, ',', '') : '—' }}</div>
                        <div class="flex-grow-1">
                            @foreach([5,4,3,2,1] as $nota)
                                @php($qtd = $item['distribuicao'][$nota] ?? 0)
                                @php($pct = $item['total'] > 0 ? round($qtd / $item['total'] * 100) : 0)
                                <div class="d-flex align-items-center small mb-1">
                                    <span class="text-nowrap" style="width:70px;">{{ $nota }} estrela(s)</span>
                                    <div class="progress flex-grow-1 mx-2" style="height:10px;">
                                        <div class="progress-bar bg-{{ $nota >= 4 ? 'success' : ($nota === 3 ? 'warning' : 'danger') }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="text-muted" style="width:90px;">{{ $qtd }} ({{ $pct }}%)</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($p->tipo === 'sim_nao')
                    <div class="d-flex align-items-center" style="gap:2rem;">
                        <span class="badge badge-success p-2">Sim: {{ $item['sim'] }}</span>
                        <span class="badge badge-danger p-2">Não: {{ $item['nao'] }}</span>
                    </div>
                @else
                    @forelse($item['textos'] as $t)
                        <div class="border-left pl-3 mb-2">
                            <small class="text-muted">{{ $t['usuario'] ?? 'Usuário' }}:</small>
                            <div>{{ $t['texto'] }}</div>
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

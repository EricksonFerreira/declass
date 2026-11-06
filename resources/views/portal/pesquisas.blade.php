@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Pesquisas abertas das escolas dos seus alunos" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @forelse($pesquisas as $pesquisa)
            <div class="list-group-item d-flex align-items-start flex-wrap" style="gap:.5rem;">
                <div class="flex-grow-1">
                    <strong>{{ $pesquisa->titulo }}</strong>
                    <span class="badge badge-success ml-1">Aberta</span>
                    <div class="small text-muted mt-1">
                        <i class="fa fa-calendar"></i>
                        Responda até {{ $pesquisa->data_fim->format('d/m/Y') }}
                        · {{ $pesquisa->escola->nome ?? 'Rede toda' }}
                        · {{ $pesquisa->perguntas->count() }} pergunta(s)
                    </div>
                    @if($pesquisa->descricao)
                        <div class="small mt-1">{{ \Illuminate\Support\Str::limit($pesquisa->descricao, 200) }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <a href="{{ route('portal.pesquisa.responder', $pesquisa->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-clipboard-list"></i> Responder
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-clipboard-question fa-3x mb-3 d-block"></i>
                Nenhuma pesquisa aberta no momento.
            </div>
        @endforelse
    </div>
</div>
@stop

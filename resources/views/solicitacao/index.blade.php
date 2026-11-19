@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Pedidos feitos pelas famílias no portal" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .soli-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); padding:.9rem; }
        .soli-cards .ux-card__anexos { display:flex; flex-wrap:wrap; gap:.3rem; }
        .soli-cards .ux-card__resp { border-radius:8px; background:#f0fdf4; border:1px solid #bbf7d0; padding:.45rem .6rem; font-size:.75rem; color:#166534; }
        .soli-cards .ux-card__msgs { font-size:.72rem; color:#9ca3af; }
    </style>
@endpush

@section('content')
@php
    $statusBadge = ['aberta' => 'ux-card__badge--blue', 'em_analise' => 'ux-card__badge--amber', 'atendida' => 'ux-card__badge--green', 'negada' => 'ux-card__badge--red'];
    $iniciais = function (string $nome): string {
        $partes = preg_split('/\s+/', trim($nome)) ?: [];
        return collect($partes)->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    };
@endphp
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @can('tipo_solicitacao.index')
            <a href="{{ route('tipo_solicitacao.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list-ul"></i> Tipos de solicitação
            </a>
        @endcan
    </div>

    <div class="card-body p-0">
        <div class="ux-cards soli-cards">
            @forelse($solicitacoes as $s)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $iniciais($s->aluno->pessoa->nome ?? ($s->autor->nome ?? '?')) }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__id">{{ $s->tipo_label }} · {{ $s->created_at?->format('d/m/Y H:i') }}</div>
                            <div class="ux-card__title">{{ $s->titulo }}</div>
                            <div class="ux-card__sub">{{ $s->aluno->pessoa->nome ?? 'Sem aluno' }} · por {{ $s->autor->nome ?? '' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $statusBadge[$s->status] ?? 'ux-card__badge--gray' }}">{{ $s->status_label }}</span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        @if($s->descricao)
                            <div class="ux-card__meta-row ux-card__meta-row--full">
                                <span class="ux-card__meta-value">{{ $s->descricao }}</span>
                            </div>
                        @endif
                        @if($s->dados)
                            @foreach($s->dados as $item)
                                <div class="ux-card__meta-row">
                                    <span class="ux-card__meta-label">{{ $item['campo'] ?? '' }}</span>
                                    <span class="ux-card__meta-value">{{ $item['valor'] ?? '' }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    @php $anexosAbertura = $s->anexos->whereNull('mensagem_id'); @endphp
                    @if($anexosAbertura->isNotEmpty())
                        <div class="ux-card__anexos px-3 pb-2">
                            @foreach($anexosAbertura as $anexo)
                                <a href="{{ route('solicitacao.anexo', [$s->id, $anexo->id]) }}" target="_blank" class="btn btn-xs btn-outline-secondary">
                                    <i class="fas fa-paperclip"></i> {{ $anexo->nome_exibicao }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if($s->resposta)
                        <div class="px-3 pb-2">
                            <div class="ux-card__resp">
                                <strong>Resposta ({{ $s->respondidoPor->nome ?? '' }} · {{ $s->respondida_em?->format('d/m/Y') }}):</strong> {{ $s->resposta }}
                            </div>
                        </div>
                    @endif
                    <div class="ux-card__foot">
                        @if($s->mensagens->isNotEmpty())
                            <span class="ux-card__msgs mr-auto"><i class="far fa-comments"></i> {{ $s->mensagens->count() }} {{ $s->mensagens->count() === 1 ? 'mensagem' : 'mensagens' }}</span>
                        @else
                            <span class="mr-auto"></span>
                        @endif
                        <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#resp{{ $s->id }}">
                            <i class="fas fa-reply"></i> Tratar
                        </button>
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    Nenhuma solicitação.
                </div>
            @endforelse
        </div>
    </div>

    @foreach($solicitacoes as $s)
        <div class="modal fade" id="resp{{ $s->id }}" tabindex="-1">
            <div class="modal-dialog"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tratar: {{ $s->titulo }}</h5></div>
                <div class="modal-body">
                    @if($s->mensagens->isNotEmpty())
                        <div class="mb-3" style="max-height:220px; overflow-y:auto;">
                            @foreach($s->mensagens->sortBy('created_at') as $mensagem)
                                @php $daFamilia = $mensagem->user_id === $s->user_id; @endphp
                                <div class="border rounded p-2 mb-1 {{ $daFamilia ? 'bg-light' : '' }}">
                                    <small class="text-muted">
                                        <strong>{{ $daFamilia ? 'Família' : ($mensagem->autor->nome ?? 'Secretaria') }}</strong>
                                        · {{ $mensagem->created_at?->format('d/m/Y H:i') }}
                                    </small>
                                    <div class="small">{{ $mensagem->mensagem }}</div>
                                    @if($mensagem->anexos->isNotEmpty())
                                        <div class="mt-1">
                                            @foreach($mensagem->anexos as $anexo)
                                                <a href="{{ route('solicitacao.anexo', [$s->id, $anexo->id]) }}" target="_blank" class="btn btn-xs btn-outline-secondary mr-1">
                                                    <i class="fas fa-paperclip"></i> {{ $anexo->nome_exibicao }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($s->conversa_aberta)
                        <form method="POST" action="{{ route('solicitacao.mensagem', $s->id) }}" class="mb-3">
                            @csrf
                            <div class="form-group mb-1">
                                <label>Perguntar à família <small class="text-muted">(pedir documento ou esclarecimento — não encerra)</small></label>
                                <textarea name="mensagem" class="form-control" rows="2" maxlength="3000" required placeholder="Ex.: enviar comprovante de residência atualizado"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-paper-plane mr-1"></i> Enviar pergunta</button>
                        </form>
                        <hr>
                    @endif

                    <form method="POST" action="{{ route('solicitacao.responder', $s->id) }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Situação</label>
                            <select name="status" class="form-control" required>
                                @foreach(['em_analise' => 'Em análise', 'atendida' => 'Atendida', 'negada' => 'Negada'] as $k => $label)
                                    <option value="{{ $k }}" {{ $s->status === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Resposta final à família</label>
                            <textarea name="resposta" class="form-control" rows="4" maxlength="3000" placeholder="Obrigatória quando atendida">{{ $s->resposta }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end mt-3" style="gap:.5rem;">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div></div>
        </div>
    @endforeach

    @if($solicitacoes->hasPages())<div class="card-footer">{{ $solicitacoes->links() }}</div>@endif
</div>
@stop

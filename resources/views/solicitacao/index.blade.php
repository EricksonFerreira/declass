@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Pedidos feitos pelas famílias no portal" />
@stop

@section('content')
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
    <div class="list-group list-group-flush">
        @forelse($solicitacoes as $s)
            @php $cores = ['aberta' => 'info', 'em_analise' => 'warning', 'atendida' => 'success', 'negada' => 'danger']; @endphp
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <strong>{{ $s->titulo }}</strong>
                        <span class="badge badge-{{ $cores[$s->status] ?? 'secondary' }} ml-1">{{ $s->status_label }}</span>
                        <div class="text-muted small">
                            {{ $s->tipo_label }} · {{ $s->aluno->pessoa->nome ?? 'Sem aluno' }}
                            · por {{ $s->autor->nome ?? '' }} · {{ $s->created_at?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#resp{{ $s->id }}">
                        <i class="fas fa-reply"></i> Tratar
                    </button>
                </div>
                @if($s->descricao)<p class="mb-1 mt-1 small">{{ $s->descricao }}</p>@endif
                @if($s->dados)
                    <ul class="small mb-1 mt-1 pl-3">
                        @foreach($s->dados as $item)
                            <li><strong>{{ $item['campo'] ?? '' }}:</strong> {{ $item['valor'] ?? '' }}</li>
                        @endforeach
                    </ul>
                @endif
                @php $anexosAbertura = $s->anexos->whereNull('mensagem_id'); @endphp
                @if($anexosAbertura->isNotEmpty())
                    <div class="mt-1">
                        @foreach($anexosAbertura as $anexo)
                            <a href="{{ route('solicitacao.anexo', [$s->id, $anexo->id]) }}" target="_blank" class="btn btn-xs btn-outline-secondary mr-1">
                                <i class="fas fa-paperclip"></i> {{ $anexo->nome_exibicao }}
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($s->mensagens->isNotEmpty())
                    <small class="text-muted"><i class="far fa-comments"></i> {{ $s->mensagens->count() }} {{ $s->mensagens->count() === 1 ? 'mensagem' : 'mensagens' }} na conversa</small>
                @endif
                @if($s->resposta)
                    <div class="alert alert-success mt-2 mb-0 py-2 small">
                        <strong>Resposta ({{ $s->respondidoPor->nome ?? '' }} · {{ $s->respondida_em?->format('d/m/Y') }}):</strong> {{ $s->resposta }}
                    </div>
                @endif
            </div>

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
        @empty
            <div class="list-group-item text-muted text-center py-4">Nenhuma solicitação.</div>
        @endforelse
    </div>
    @if($solicitacoes->hasPages())<div class="card-footer">{{ $solicitacoes->links() }}</div>@endif
</div>
@stop

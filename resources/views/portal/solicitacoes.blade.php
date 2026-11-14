@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Pedidos à secretaria: declarações, atendimento, financeiro" />
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

<div id="solicitacoes-accordion">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <a class="font-weight-bold d-block" data-toggle="collapse" href="#nova-solicitacao" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                <i class="fas fa-plus mr-1"></i> Nova solicitação
            </a>
        </div>
        <div id="nova-solicitacao" class="collapse {{ $errors->any() ? 'show' : '' }}" data-parent="#solicitacoes-accordion">
            <div class="card-body">
                @include('portal.partials.solicitacao-form')
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <a class="font-weight-bold d-block" data-toggle="collapse" href="#minhas-solicitacoes">
                <i class="fas fa-list mr-1"></i> Minhas solicitações
            </a>
        </div>
        <div id="minhas-solicitacoes" class="collapse show" data-parent="#solicitacoes-accordion">
            <div class="list-group list-group-flush">
                @forelse($solicitacoes as $solicitacao)
                    @php $cores = ['aberta' => 'info', 'em_analise' => 'warning', 'atendida' => 'success', 'negada' => 'danger']; @endphp
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-1">
                            <a class="text-dark" data-toggle="collapse" href="#sol{{ $solicitacao->id }}">
                                <strong>{{ $solicitacao->titulo }}</strong>
                                <i class="fas fa-chevron-down small text-muted ml-1"></i>
                            </a>
                            <span class="badge badge-{{ $cores[$solicitacao->status] ?? 'secondary' }}">{{ \App\Models\Solicitacao::STATUS[$solicitacao->status] ?? $solicitacao->status }}</span>
                        </div>
                        <small class="text-muted">
                            {{ $solicitacao->tipo_label }}
                            · {{ $solicitacao->aluno->pessoa->nome ?? '' }}
                            · aberta em {{ $solicitacao->created_at?->format('d/m/Y') }}
                            @if($solicitacao->mensagens->isNotEmpty())
                                · <i class="far fa-comments"></i> {{ $solicitacao->mensagens->count() }} {{ $solicitacao->mensagens->count() === 1 ? 'mensagem' : 'mensagens' }}
                            @endif
                        </small>

                        <div class="collapse mt-2" id="sol{{ $solicitacao->id }}">
                            @if($solicitacao->descricao)
                                <p class="mb-1">{{ $solicitacao->descricao }}</p>
                            @endif
                            @if($solicitacao->dados)
                                <ul class="small mb-1 mt-1 pl-3">
                                    @foreach($solicitacao->dados as $item)
                                        <li><strong>{{ $item['campo'] ?? '' }}:</strong> {{ $item['valor'] ?? '' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @php $anexosAbertura = $solicitacao->anexos->whereNull('mensagem_id'); @endphp
                            @if($anexosAbertura->isNotEmpty())
                                <div class="mt-1">
                                    @foreach($anexosAbertura as $anexo)
                                        <a href="{{ route('portal.solicitacoes.anexo', [$solicitacao->id, $anexo->id]) }}" target="_blank" class="btn btn-xs btn-outline-secondary mr-1">
                                            <i class="fas fa-paperclip"></i> {{ $anexo->nome_exibicao }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if($solicitacao->mensagens->isNotEmpty())
                                <div class="mt-2">
                                    @foreach($solicitacao->mensagens->sortBy('created_at') as $mensagem)
                                        @php $daFamilia = $mensagem->user_id === $solicitacao->user_id; @endphp
                                        <div class="border rounded p-2 mb-2 {{ $daFamilia ? 'bg-light' : 'border-primary' }}">
                                            <small class="text-muted">
                                                <strong>{{ $daFamilia ? 'Você' : 'Secretaria' }}</strong>
                                                · {{ $mensagem->created_at?->format('d/m/Y H:i') }}
                                            </small>
                                            <div class="small">{{ $mensagem->mensagem }}</div>
                                            @if($mensagem->anexos->isNotEmpty())
                                                <div class="mt-1">
                                                    @foreach($mensagem->anexos as $anexo)
                                                        <a href="{{ route('portal.solicitacoes.anexo', [$solicitacao->id, $anexo->id]) }}" target="_blank" class="btn btn-xs btn-outline-secondary mr-1">
                                                            <i class="fas fa-paperclip"></i> {{ $anexo->nome_exibicao }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($solicitacao->conversa_aberta)
                                <form method="POST" action="{{ route('portal.solicitacoes.mensagem', $solicitacao->id) }}" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <div class="form-group mb-1">
                                        <textarea name="mensagem" class="form-control" rows="2" maxlength="3000" required placeholder="Responder à secretaria…"></textarea>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                        <input type="file" name="anexos[]" class="form-control-file flex-grow-1" multiple accept=".jpg,.jpeg,.png,.pdf" style="max-width:260px;">
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-reply mr-1"></i> Responder</button>
                                    </div>
                                </form>
                            @else
                                <small class="text-muted d-block mt-2">Solicitação finalizada — conversa encerrada.</small>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-muted text-center py-4">Nenhuma solicitação ainda.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
// Reabre o item da conversa apos enviar uma resposta (redirecionado com #sol{id})
if (location.hash && $(location.hash).length) {
    $(location.hash).collapse('show');
    $(location.hash)[0].scrollIntoView();
}
</script>
@endpush

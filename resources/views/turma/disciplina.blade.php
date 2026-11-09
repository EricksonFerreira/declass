@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$disciplina->nome" subtitle="{{ $turma->nome }} · {{ $turma->etapa->nome ?? '' }} · {{ $turma->anoLetivo->ano ?? '' }}" />
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<ul class="nav nav-tabs" id="disciplinaTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-forum-link" data-toggle="tab" href="#tab-forum" role="tab"><i class="fas fa-comments mr-1"></i> Fórum <span class="badge badge-light border ml-1">{{ $posts->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-materiais-link" data-toggle="tab" href="#tab-materiais" role="tab"><i class="fas fa-folder-open mr-1"></i> Materiais <span class="badge badge-light border ml-1">{{ $materiais->count() }}</span></a>
    </li>
</ul>

<div class="tab-content pt-3">

<div class="tab-pane fade show active" id="tab-forum" role="tabpanel">
    <div class="card">
        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-comments mr-1"></i> Fórum de {{ $disciplina->nome }}</h3></div>
        @can('turma.forum.store')
        @if($turma->finalizada())
        <div class="card-body border-bottom">
            <small class="text-muted"><i class="fa fa-lock mr-1"></i>Turma finalizada — o fórum está somente para consulta, sem novas mensagens.</small>
        </div>
        @else
        <div class="card-body border-bottom">
            <form method="POST" action="{{ route('turma.forum.store', [$turma->id, $disciplina->id]) }}">
                @csrf
                <div class="form-group">
                    <label>Título <small class="text-muted">(opcional)</small></label>
                    <input type="text" name="titulo" class="form-control" maxlength="200" placeholder="Ex.: Dúvidas sobre a prova do dia 15">
                </div>
                <div class="form-group">
                    <label class="d-flex justify-content-between align-items-center mb-1">
                        Mensagem
                        <span class="text-nowrap">
                            <button type="button" class="btn btn-outline-secondary btn-xs fmt-btn" data-fmt="*" title="Negrito"><i class="fa fa-bold"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-xs fmt-btn" data-fmt="_" title="Itálico"><i class="fa fa-italic"></i></button>
                        </span>
                    </label>
                    <textarea name="mensagem" class="form-control" rows="2" maxlength="3000" required placeholder="Escreva a orientação para a turma..."></textarea>
                    <small class="form-text text-muted">Use *texto* para negrito e _texto_ para itálico.</small>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane mr-1"></i> Publicar orientação</button>
            </form>
        </div>
        @endif
        @endcan
        <div class="card-body">
            @forelse($posts as $post)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between flex-wrap mb-1">
                        <strong>{{ $post->autor->nome ?? '' }}</strong>
                        <span class="d-flex align-items-center" style="gap:.5rem;">
                            @if($post->estaEncerrada())
                                <span class="badge badge-secondary"><i class="fa fa-lock mr-1"></i>Encerrada</span>
                            @endif
                            <small class="text-muted">{{ $post->created_at?->format('d/m/Y H:i') }}</small>
                            <span class="dropdown">
                                <button type="button" class="btn btn-xs btn-outline-secondary" data-toggle="dropdown" title="Opções do tópico"><i class="fa fa-ellipsis-v"></i></button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @can('turma.forum.update')
                                        <button type="button" class="dropdown-item" data-toggle="collapse" data-target="#editar-titulo-{{ $post->id }}"><i class="fa fa-pen mr-2"></i>Editar título</button>
                                    @endcan
                                    @can('turma.forum.encerrar')
                                        <form method="POST" action="{{ route('turma.forum.encerrar', [$turma->id, $disciplina->id, $post->id]) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="fa {{ $post->estaEncerrada() ? 'fa-unlock' : 'fa-lock' }} mr-2"></i>{{ $post->estaEncerrada() ? 'Reabrir conversa' : 'Encerrar conversa' }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('turma.forum.destroy')
                                        <div class="dropdown-divider"></div>
                                        <form method="POST" action="{{ route('turma.forum.destroy', [$turma->id, $disciplina->id, $post->id]) }}" onsubmit="return confirm('Remover este tópico e todas as respostas?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="fa fa-trash mr-2"></i>Remover tópico</button>
                                        </form>
                                    @endcan
                                </div>
                            </span>
                        </span>
                    </div>
                    @can('turma.forum.update')
                        <form method="POST" action="{{ route('turma.forum.update', [$turma->id, $disciplina->id, $post->id]) }}" class="collapse mb-2" id="editar-titulo-{{ $post->id }}">
                            @csrf @method('PUT')
                            <div class="input-group input-group-sm">
                                <input type="text" name="titulo" class="form-control" maxlength="200" value="{{ $post->titulo }}" placeholder="Título do tópico (vazio usa o início da mensagem)">
                                <div class="input-group-append"><button type="submit" class="btn btn-outline-primary"><i class="fa fa-check"></i></button></div>
                            </div>
                        </form>
                    @endcan
                    @if($post->titulo)
                        <div class="font-weight-bold small mb-1">{{ $post->titulo }}</div>
                    @endif
                    <p class="small mb-2">{!! $post->mensagem_formatada !!}</p>
                    @foreach($post->respostas as $resposta)
                        <div class="border-left pl-3 py-1 mb-1">
                            <small><strong>{{ $resposta->autor->nome ?? '' }}</strong> <span class="text-muted">· {{ $resposta->created_at?->format('d/m/Y H:i') }}</span></small>
                            <div class="small">{!! $resposta->mensagem_formatada !!}</div>
                        </div>
                    @endforeach
                    @if($turma->finalizada())
                        <small class="text-muted"><i class="fa fa-lock mr-1"></i>Turma finalizada — sem novas respostas.</small>
                    @elseif($post->estaEncerrada())
                        <small class="text-muted"><i class="fa fa-lock mr-1"></i>Conversa encerrada — sem novas respostas.</small>
                    @else
                        @can('turma.forum.comentar')
                        <form method="POST" action="{{ route('turma.forum.comentar', [$turma->id, $disciplina->id, $post->id]) }}" class="mt-2">
                            @csrf
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary fmt-btn" data-fmt="*" title="Negrito"><i class="fa fa-bold"></i></button>
                                    <button type="button" class="btn btn-outline-secondary fmt-btn" data-fmt="_" title="Itálico"><i class="fa fa-italic"></i></button>
                                </div>
                                <input type="text" name="mensagem" class="form-control" placeholder="Responder como professor..." maxlength="2000" required>
                                <div class="input-group-append"><button class="btn btn-outline-primary" type="submit"><i class="fa fa-reply"></i> Responder</button></div>
                            </div>
                        </form>
                        @endcan
                    @endif
                </div>
            @empty
                <p class="text-muted text-center py-3 mb-0">Nenhum tópico publicado ainda.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-materiais" role="tabpanel">
    <div class="row">
        @can('turma.disciplina.material.store')
        <div class="col-lg-5">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title font-weight-bold">Publicar material</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('turma.disciplina.material.store', [$turma->id, $disciplina->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="titulo" class="form-control" maxlength="200" required placeholder="Ex.: Lista de exercícios — cap. 3">
                        </div>
                        <div class="form-group">
                            <label>Descrição <small class="text-muted">(opcional)</small></label>
                            <textarea name="descricao" class="form-control" rows="2" maxlength="2000"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Arquivo</label>
                            <input type="file" name="arquivo" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.txt">
                            <small class="text-muted">PDF, Office, imagem ou ZIP — até 15MB. Ou informe um link:</small>
                        </div>
                        <div class="form-group">
                            <input type="url" name="url" class="form-control" maxlength="500" placeholder="https://…">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-upload mr-1"></i> Publicar material</button>
                    </form>
                </div>
            </div>
        </div>
        @endcan
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title font-weight-bold">Materiais de {{ $disciplina->nome }}</h3></div>
                <div class="list-group list-group-flush">
                    @forelse($materiais as $material)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <strong>{{ $material->titulo }}</strong>
                                    <small class="text-muted d-block">{{ $material->autor->nome ?? '' }} · {{ $material->created_at?->format('d/m/Y') }}</small>
                                    @if($material->descricao)<small>{{ $material->descricao }}</small>@endif
                                </div>
                                <div class="d-flex align-items-center" style="gap:.4rem;">
                                    @if($material->arquivo_path)
                                        <a href="{{ asset('storage/'.$material->arquivo_path) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fa fa-download"></i></a>
                                    @elseif($material->url)
                                        <a href="{{ $material->url }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary"><i class="fa fa-external-link-alt"></i></a>
                                    @endif
                                    @can('turma.disciplina.material.destroy')
                                    <form method="POST" action="{{ route('turma.disciplina.material.destroy', [$turma->id, $disciplina->id, $material->id]) }}" onsubmit="return confirm('Remover este material?');" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted text-center py-4">Nenhum material publicado nesta disciplina.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@stop

@section('js')
<script>
    $(function () {
        var hash = window.location.hash;
        if (hash && $('#disciplinaTabs a[href="' + hash + '"]').length) {
            $('#disciplinaTabs a[href="' + hash + '"]').tab('show');
        }
        $('#disciplinaTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            history.replaceState(null, '', e.target.hash);
        });

        // Botoes B/I envolvem a selecao do campo com marcadores
        // estilo WhatsApp (*negrito*, _italico_); o servidor renderiza.
        $(document).on('click', '.fmt-btn', function () {
            var el = $(this).closest('form').find('[name="mensagem"]')[0];
            if (!el) {
                return;
            }
            var m = String($(this).data('fmt'));
            var ini = el.selectionStart || 0;
            var fim = el.selectionEnd || 0;
            var v = el.value;
            el.value = v.substring(0, ini) + m + v.substring(ini, fim) + m + v.substring(fim);
            el.focus();
            var pos = ini === fim ? ini + m.length : fim + m.length * 2;
            el.setSelectionRange(pos, pos);
        });
    });
</script>
@stop

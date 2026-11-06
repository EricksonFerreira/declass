@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$aluno->nome ?? 'Aluno'" subtitle="Dados do aluno e responsáveis" />
@stop

@section('content')
@php($p = $aluno->pessoa)
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header font-weight-bold">Dados Pessoais</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $p->nome ?? '—' }}</dd>
                    <dt class="col-sm-4">Nome social</dt><dd class="col-sm-8">{{ $p->nome_social ?? '—' }}</dd>
                    <dt class="col-sm-4">CPF</dt><dd class="col-sm-8">{{ $p->cpf ?? '—' }}</dd>
                    <dt class="col-sm-4">RG</dt><dd class="col-sm-8">{{ $p->rg ?? '—' }}</dd>
                    <dt class="col-sm-4">Nascimento</dt><dd class="col-sm-8">{{ optional($p->data_nascimento ?? null)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Sexo</dt><dd class="col-sm-8">{{ \App\Models\Pessoa::SEXOS[$p->sexo ?? ''] ?? '—' }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $p->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $p->telefone ?? '—' }}</dd>
                    <dt class="col-sm-4">Celular</dt><dd class="col-sm-8">{{ $p->celular ?? '—' }}</dd>
                    <dt class="col-sm-4">Endereço</dt>
                    <dd class="col-sm-8">
                        {{ collect([$p->endereco ?? null, $p->cidade ?? null, $p->estado ?? null])->filter()->implode(', ') ?: '—' }}
                        @if($p->cep) <span class="text-muted">(CEP {{ $p->cep }})</span> @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Dados Escolares</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">RA</dt><dd class="col-sm-8">{{ $aluno->registro_academico ?? '—' }}</dd>
                    <dt class="col-sm-4">Escola/unidade</dt><dd class="col-sm-8">{{ $aluno->escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-4">Data de ingresso</dt><dd class="col-sm-8">{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Turno preferencial</dt><dd class="col-sm-8">{{ \App\Models\Aluno::TURNOS[$aluno->turno_preferencial ?? ''] ?? '—' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($aluno->estaAtivo())
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-secondary">Inativo</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Necessidades especiais</dt><dd class="col-sm-8">{{ $aluno->necessidades_especiais ?? '—' }}</dd>
                    <dt class="col-sm-4">Saúde</dt><dd class="col-sm-8">{{ $aluno->informacoes_saude ?? '—' }}</dd>
                    <dt class="col-sm-4">Emergência</dt><dd class="col-sm-8">{{ $aluno->contatos_emergencia ?? '—' }}</dd>
                    <dt class="col-sm-4">Restrições de acesso</dt><dd class="col-sm-8">{{ $aluno->restricoes_acesso ?? '—' }}</dd>
                    <dt class="col-sm-4">Observações</dt><dd class="col-sm-8">{{ $aluno->observacoes ?? '—' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('aluno.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('aluno.show')
                    <a href="{{ route('aluno.declaracao', $aluno->id) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-file-pdf"></i> Declaração</a>
                @endcan
                @can('saude.index')
                    <a href="{{ route('saude.edit', $aluno->id) }}" class="btn btn-outline-danger btn-sm"><i class="fa fa-notes-medical"></i> Ficha de Saúde</a>
                @endcan
                @can('aluno.edit')
                    <a href="{{ route('aluno.edit', $aluno->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header font-weight-bold">Responsáveis ({{ $vinculos->count() }})</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($vinculos as $v)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <a href="{{ route('responsavel.show', $v->responsavel_id) }}" class="font-weight-bold">{{ $v->pessoa_nome }}</a>
                                    <div>
                                        <span class="badge badge-info">{{ $tiposResponsavel[$v->tipo] ?? $v->tipo }}</span>
                                        @if($v->principal)<span class="badge badge-primary">Principal</span>@endif
                                        @if($v->pode_retirar)<span class="badge badge-success">Pode retirar</span>@endif
                                        @if(!$v->autoriza_contato)<span class="badge badge-secondary">Sem contato</span>@endif
                                    </div>
                                    @if($v->restricoes)<small class="text-muted d-block">Restrições: {{ $v->restricoes }}</small>@endif
                                    @if($v->observacoes)<small class="text-muted d-block">{{ $v->observacoes }}</small>@endif
                                </div>
                                @can('aluno.responsavel.update')
                                <details class="ml-2">
                                    <summary class="btn btn-xs btn-outline-secondary" style="cursor:pointer;list-style:none;">Editar</summary>
                                    <form method="POST" action="{{ route('aluno.responsavel.update', [$aluno->id, $v->id]) }}" class="mt-2" style="min-width:220px;">
                                        @csrf @method('PUT')
                                        <div class="form-check">
                                            <input type="hidden" name="principal" value="0">
                                            <input type="checkbox" name="principal" value="1" class="form-check-input" id="vp{{ $v->id }}" {{ $v->principal ? 'checked' : '' }}>
                                            <label class="form-check-label" for="vp{{ $v->id }}">Principal</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="hidden" name="pode_retirar" value="0">
                                            <input type="checkbox" name="pode_retirar" value="1" class="form-check-input" id="vr{{ $v->id }}" {{ $v->pode_retirar ? 'checked' : '' }}>
                                            <label class="form-check-label" for="vr{{ $v->id }}">Pode retirar</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="hidden" name="autoriza_contato" value="0">
                                            <input type="checkbox" name="autoriza_contato" value="1" class="form-check-input" id="vc{{ $v->id }}" {{ $v->autoriza_contato ? 'checked' : '' }}>
                                            <label class="form-check-label" for="vc{{ $v->id }}">Autoriza contato</label>
                                        </div>
                                        <div class="form-group mt-1">
                                            <input name="restricoes" class="form-control form-control-sm" placeholder="Restrições" value="{{ $v->restricoes }}">
                                        </div>
                                        <button class="btn btn-xs btn-primary btn-block">Salvar</button>
                                    </form>
                                </details>
                                @endcan
                            </div>
                            @can('aluno.responsavel.destroy')
                                <form method="POST" action="{{ route('aluno.responsavel.destroy', [$aluno->id, $v->id]) }}" onsubmit="return confirm('Remover este vínculo?');" class="mt-1">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger">Remover vínculo</button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">Nenhum responsável vinculado.</li>
                    @endforelse
                </ul>
            </div>

            @can('aluno.responsavel.store')
            <div class="card-footer">
                <form method="POST" action="{{ route('aluno.responsavel.store', $aluno->id) }}">
                    @csrf
                    <div class="form-group mb-2">
                        <label class="form-label">Vincular responsável</label>
                        <select name="responsavel_id" class="form-control form-control-sm" required>
                            <option value="">Selecionar responsável...</option>
                            @foreach($responsaveisOpcoes as $rid => $rnome)
                                <option value="{{ $rid }}">{{ $rnome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <select name="tipo" class="form-control form-control-sm">
                            @foreach($tiposResponsavel as $tk => $tl)
                                <option value="{{ $tk }}">{{ $tl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="principal" value="0">
                        <input type="checkbox" name="principal" value="1" class="form-check-input" id="nvP">
                        <label class="form-check-label" for="nvP">Principal</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="pode_retirar" value="0">
                        <input type="checkbox" name="pode_retirar" value="1" class="form-check-input" id="nvR">
                        <label class="form-check-label" for="nvR">Pode retirar</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="autoriza_contato" value="0">
                        <input type="checkbox" name="autoriza_contato" value="1" class="form-check-input" id="nvC" checked>
                        <label class="form-check-label" for="nvC">Autoriza contato</label>
                    </div>
                    <button class="btn btn-sm btn-primary btn-block mt-2">Vincular</button>
                </form>
            </div>
            @endcan
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Documentos ({{ $aluno->documentos->count() }})</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($aluno->documentos->sortByDesc('id') as $doc)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="font-weight-bold">{{ $doc->titulo }}</span>
                                    <div>
                                        <span class="badge badge-secondary">{{ $tiposDocumento[$doc->tipo] ?? $doc->tipo }}</span>
                                        @if($doc->arquivo_path)
                                            <span class="badge badge-success">Anexado</span>
                                        @else
                                            <span class="badge badge-warning">Sem arquivo</span>
                                        @endif
                                    </div>
                                    @if($doc->descricao)<small class="text-muted d-block">{{ $doc->descricao }}</small>@endif
                                </div>
                                <div class="text-right text-nowrap">
                                    @if($doc->arquivo_path)
                                        @can('aluno.documento.arquivo')
                                            <a href="{{ route('aluno.documento.arquivo', [$aluno->id, $doc->id]) }}" class="btn btn-xs btn-outline-primary" title="Download"><i class="fa fa-download"></i></a>
                                        @endcan
                                    @endif
                                    @can('aluno.documento.update')
                                    <details class="d-inline-block ml-1">
                                        <summary class="btn btn-xs btn-outline-secondary" style="cursor:pointer;list-style:none;">Editar</summary>
                                        <form method="POST" action="{{ route('aluno.documento.update', [$aluno->id, $doc->id]) }}" enctype="multipart/form-data" class="mt-2 text-left" style="min-width:240px;">
                                            @csrf @method('PUT')
                                            <select name="tipo" class="form-control form-control-sm mb-1">
                                                @foreach($tiposDocumento as $tk => $tl)
                                                    <option value="{{ $tk }}" @selected($doc->tipo === $tk)>{{ $tl }}</option>
                                                @endforeach
                                            </select>
                                            <input name="titulo" class="form-control form-control-sm mb-1" value="{{ $doc->titulo }}" required maxlength="150">
                                            <input name="descricao" class="form-control form-control-sm mb-1" placeholder="Descrição" value="{{ $doc->descricao }}" maxlength="2000">
                                            <input type="file" name="arquivo" class="form-control-file form-control-sm mb-1" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            <button class="btn btn-xs btn-primary btn-block">Salvar</button>
                                        </form>
                                    </details>
                                    @endcan
                                </div>
                            </div>
                            @can('aluno.documento.destroy')
                                <form method="POST" action="{{ route('aluno.documento.destroy', [$aluno->id, $doc->id]) }}" onsubmit="return confirm('Excluir este documento? O arquivo também será removido.');" class="mt-1">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger">Excluir documento</button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">Nenhum documento anexado.</li>
                    @endforelse
                </ul>
            </div>

            @can('aluno.documento.store')
            <div class="card-footer">
                <form method="POST" action="{{ route('aluno.documento.store', $aluno->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-2">
                        <label class="form-label">Anexar documento</label>
                        <select name="tipo" class="form-control form-control-sm" required>
                            <option value="">Tipo do documento...</option>
                            @foreach($tiposDocumento as $tk => $tl)
                                <option value="{{ $tk }}">{{ $tl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <input name="titulo" class="form-control form-control-sm" placeholder="Título (ex.: RG frente)" required maxlength="150">
                    </div>
                    <div class="form-group mb-2">
                        <input name="descricao" class="form-control form-control-sm" placeholder="Descrição (opcional)" maxlength="2000">
                    </div>
                    <div class="form-group mb-2">
                        <input type="file" name="arquivo" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        <small class="text-muted">PDF, imagem ou Word — máx. 10 MB</small>
                    </div>
                    <button class="btn btn-sm btn-primary btn-block">Anexar</button>
                </form>
            </div>
            @endcan
        </div>
    </div>
</div>
@stop

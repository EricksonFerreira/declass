@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$aluno->nome ?? 'Aluno'" subtitle="Ficha do aluno" />
@stop

@push('css')
<style>
    .as-perfil { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; }
    .as-avatar { width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.35rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#2563eb,#7c3aed); flex-shrink:0; }
    .as-nome { font-size:1.15rem; font-weight:700; margin-bottom:2px; }
    .as-meta { font-size:.78rem; color:#6b7280; }
    .as-acoes { margin-left:auto; display:flex; gap:.35rem; flex-wrap:wrap; }
    .as-tabs { border-bottom:2px solid #e5e7eb; }
    .as-tabs .nav-link { border:0; font-size:.82rem; font-weight:700; color:#6b7280; padding:.55rem .9rem; }
    .as-tabs .nav-link.active { color:#2563eb; border-bottom:2px solid #2563eb; margin-bottom:-2px; }
    .as-sec { font-size:.76rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin:1rem 0 .5rem; }
    .as-sec:first-child { margin-top:0; }
    .as-info { display:grid; grid-template-columns:1fr; gap:.35rem; }
    .as-item { display:flex; gap:.55rem; align-items:flex-start; padding:.42rem .6rem; border:1px solid #eef1f6; border-radius:8px; background:#fff; }
    .as-item i { color:#2563eb; width:14px; margin-top:3px; text-align:center; }
    .as-item label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin:0; }
    .as-item span { font-size:.82rem; color:#1f2937; font-weight:600; }
    .as-resp, .as-doc { border:1px solid #eef1f6; border-radius:10px; padding:.65rem .8rem; margin-bottom:.55rem; }
    .as-resp:hover, .as-doc:hover { background:#f8fafc; }
</style>
@endpush

@section('content')
@php
    $p = $aluno->pessoa;
    $iniciais = collect(explode(' ', trim($p->nome ?? '?')))->filter()->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->take(2)->implode('');
@endphp

{{-- Header do perfil --}}
<div class="card">
    <div class="card-body py-3 as-perfil">
        <div class="as-avatar">{{ $iniciais }}</div>
        <div>
            <div class="as-nome">{{ $p->nome ?? '—' }}</div>
            <div class="as-meta">
                RA {{ $aluno->registro_academico ?? '—' }} · {{ $aluno->escola->nome ?? '—' }}
                <span class="badge badge-{{ $aluno->esta_ativo ? 'success' : 'secondary' }} ml-1">{{ $aluno->esta_ativo ? 'Ativo' : 'Inativo' }}</span>
            </div>
        </div>
        <div class="as-acoes">
            @can('aluno.show')
                <a href="{{ route('aluno.historico', $aluno->id) }}" class="btn btn-info btn-sm"><i class="fa fa-history"></i> Histórico</a>
                <a href="{{ route('aluno.declaracao', $aluno->id) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-file-pdf"></i> Declaração</a>
                @if($aluno->matriculas->contains(fn ($m) => $m->status === 'concluida' && $m->turma?->etapa?->emite_certificado))
                    <a href="{{ route('aluno.certificado', $aluno->id) }}" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-certificate"></i> Certificado</a>
                @endif
            @endcan
            @can('saude.show')
                <button type="button" id="btnFichaSaude" class="btn btn-outline-danger btn-sm"><i class="fa fa-notes-medical"></i> Ficha de Saúde</button>
            @endcan
            @can('aluno.edit')
                <a href="{{ route('aluno.edit', $aluno->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Editar</a>
            @endcan
        </div>
    </div>
</div>

<div class="card">
    <ul class="nav nav-tabs as-tabs px-2 pt-2" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#as-dados"><i class="fa fa-user mr-1"></i> Dados</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#as-resp"><i class="fa fa-users mr-1"></i> Responsáveis <span class="badge badge-light border ml-1">{{ $vinculos->count() }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#as-docs"><i class="fa fa-folder-open mr-1"></i> Documentos <span class="badge badge-light border ml-1">{{ $aluno->documentos->count() }}</span></a></li>
        @can('saude.show')
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#as-saude"><i class="fa fa-notes-medical mr-1"></i> Saúde @if($ficha?->tem_alertas)<span class="badge badge-danger ml-1" title="Possui alertas de saúde">!</span>@endif</a></li>
        @endcan
    </ul>

    <div class="tab-content">
        {{-- Aba: dados pessoais + escolares --}}
        <div class="tab-pane fade show active" id="as-dados">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
@php
    $idade = ($p->data_nascimento ?? null)?->age;
    $info = function (string $icon, string $label, $valor) {
        return '<div class="as-item"><i class="fa '.$icon.'"></i><div><label>'.$label.'</label><span>'.e($valor ?: '—').'</span></div></div>';
    };
@endphp
                        <div class="as-sec"><i class="fa fa-user mr-1"></i> Dados pessoais</div>
                        <div class="as-info">
                            {!! $info('fa-user', 'Nome', $p->nome ?? null) !!}
                            {!! $info('fa-user-tag', 'Nome social', $p->nome_social ?? null) !!}
                            {!! $info('fa-id-card', 'CPF', $p->cpf ?? null) !!}
                            {!! $info('fa-address-card', 'RG', $p->rg ?? null) !!}
                            {!! $info('fa-birthday-cake', 'Nascimento', optional($p->data_nascimento ?? null)->format('d/m/Y').($idade !== null ? " ({$idade} anos)" : '')) !!}
                            {!! $info('fa-venus-mars', 'Sexo', \App\Models\Pessoa::SEXOS[$p->sexo ?? ''] ?? null) !!}
                        </div>
                        <div class="as-sec"><i class="fa fa-address-book mr-1"></i> Contato e endereço</div>
                        <div class="as-info">
                            {!! $info('fa-envelope', 'E-mail', $p->email ?? null) !!}
                            {!! $info('fa-phone', 'Telefone', $p->telefone ?? null) !!}
                            {!! $info('fa-mobile-alt', 'Celular', $p->celular ?? null) !!}
                            {!! $info('fa-map-marker-alt', 'Endereço', (collect([$p->endereco ?? null, $p->cidade ?? null, $p->estado ?? null])->filter()->implode(', ') ?: null).($p->cep ? ' — CEP '.$p->cep : '')) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="as-sec"><i class="fa fa-graduation-cap mr-1"></i> Dados escolares</div>
                        <div class="as-info">
                            {!! $info('fa-school', 'Escola/unidade', $aluno->escola->nome ?? null) !!}
                            {!! $info('fa-calendar-check', 'Data de ingresso', optional($aluno->data_ingresso)->format('d/m/Y')) !!}
                            {!! $info('fa-sun', 'Turno preferencial', \App\Models\Aluno::TURNOS[$aluno->turno_preferencial ?? ''] ?? null) !!}
                        </div>
                        <div class="as-sec"><i class="fa fa-heartbeat mr-1"></i> Saúde e observações</div>
                        <div class="as-info">
                            {!! $info('fa-universal-access', 'Necessidades', $aluno->necessidades_especiais) !!}
                            {!! $info('fa-notes-medical', 'Saúde', $aluno->informacoes_saude) !!}
                            {!! $info('fa-phone-volume', 'Emergência', $aluno->contatos_emergencia) !!}
                            {!! $info('fa-ban', 'Restrições', $aluno->restricoes_acesso) !!}
                            {!! $info('fa-sticky-note', 'Observações', $aluno->observacoes) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aba: responsaveis --}}
        <div class="tab-pane fade" id="as-resp">
            <div class="card-body">
                @forelse($vinculos as $v)
                    <div class="as-resp d-flex justify-content-between align-items-start">
                        <div>
                            <a href="{{ route('responsavel.show', $v->responsavel_id) }}" class="font-weight-bold">{{ $v->pessoa_nome }}</a>
                            <div class="mt-1">
                                <span class="badge badge-info">{{ $tiposResponsavel[$v->tipo] ?? $v->tipo }}</span>
                                @if($v->principal)<span class="badge badge-primary">Principal</span>@endif
                                @if($v->pode_retirar)<span class="badge badge-success">Pode retirar</span>@endif
                                @if(!$v->autoriza_contato)<span class="badge badge-secondary">Sem contato</span>@endif
                            </div>
                            @if($v->restricoes)<small class="text-muted d-block mt-1">Restrições: {{ $v->restricoes }}</small>@endif
                            @if($v->observacoes)<small class="text-muted d-block">{{ $v->observacoes }}</small>@endif
                        </div>
                        <div class="text-nowrap ml-2">
                            @can('aluno.responsavel.update')
                            <details class="d-inline-block">
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
                                    <div class="form-group mt-1 mb-1">
                                        <input name="restricoes" class="form-control form-control-sm" placeholder="Restrições" value="{{ $v->restricoes }}">
                                    </div>
                                    <button class="btn btn-xs btn-primary btn-block">Salvar</button>
                                </form>
                            </details>
                            @endcan
                            @can('aluno.responsavel.destroy')
                                <form method="POST" action="{{ route('aluno.responsavel.destroy', [$aluno->id, $v->id]) }}" onsubmit="return confirm('Remover este vínculo?');" class="d-inline ml-1">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="fa fa-times"></i></button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">Nenhum responsável vinculado.</p>
                @endforelse

                @can('aluno.responsavel.store')
                    <div class="border rounded p-3 mt-3" style="background:#f8fafc;">
                        <div class="as-sec"><i class="fa fa-user-plus mr-1"></i> Vincular responsável</div>
                        <form method="POST" action="{{ route('aluno.responsavel.store', $aluno->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="resp_display" class="form-control" placeholder="Selecionar responsável..." readonly tabindex="-1">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary" id="btnBuscarResp"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="responsavel_id" id="responsavel_id">
                                </div>
                                <div class="col-md-5">
                                    <select name="tipo" class="form-control form-control-sm">
                                        @foreach($tiposResponsavel as $tk => $tl)
                                            <option value="{{ $tk }}">{{ $tl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex mt-2" style="gap:1rem;">
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
                                <button class="btn btn-sm btn-primary ml-auto">Vincular</button>
                            </div>
                        </form>
                    </div>
                @endcan
            </div>
        </div>

        {{-- Aba: documentos --}}
        <div class="tab-pane fade" id="as-docs">
            <div class="card-body">
                @forelse($aluno->documentos->sortByDesc('id') as $doc)
                    <div class="as-doc d-flex justify-content-between align-items-start">
                        <div>
                            <span class="font-weight-bold"><i class="fa fa-file-alt text-primary mr-1"></i> {{ $doc->titulo }}</span>
                            <div class="mt-1">
                                <span class="badge badge-secondary">{{ $tiposDocumento[$doc->tipo] ?? $doc->tipo }}</span>
                                @if($doc->arquivo_path)
                                    <span class="badge badge-success">Anexado</span>
                                @else
                                    <span class="badge badge-warning">Sem arquivo</span>
                                @endif
                            </div>
                            @if($doc->descricao)<small class="text-muted d-block mt-1">{{ $doc->descricao }}</small>@endif
                        </div>
                        <div class="text-right text-nowrap ml-2">
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
                            @can('aluno.documento.destroy')
                                <form method="POST" action="{{ route('aluno.documento.destroy', [$aluno->id, $doc->id]) }}" onsubmit="return confirm('Excluir este documento? O arquivo também será removido.');" class="d-inline ml-1">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="fa fa-times"></i></button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">Nenhum documento anexado.</p>
                @endforelse

                @can('aluno.documento.store')
                    <div class="border rounded p-3 mt-3" style="background:#f8fafc;">
                        <div class="as-sec"><i class="fa fa-upload mr-1"></i> Anexar documento</div>
                        <form method="POST" action="{{ route('aluno.documento.store', $aluno->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="tipo" class="form-control form-control-sm" required>
                                        <option value="">Tipo do documento...</option>
                                        @foreach($tiposDocumento as $tk => $tl)
                                            <option value="{{ $tk }}">{{ $tl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input name="titulo" class="form-control form-control-sm" placeholder="Título (ex.: RG frente)" required maxlength="150">
                                </div>
                                <div class="col-md-4">
                                    <input name="descricao" class="form-control form-control-sm" placeholder="Descrição (opcional)" maxlength="2000">
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-2">
                                <input type="file" name="arquivo" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                <small class="text-muted ml-auto mr-2">PDF, imagem ou Word — máx. 10 MB</small>
                                <button class="btn btn-sm btn-primary">Anexar</button>
                            </div>
                        </form>
                    </div>
                @endcan
            </div>
        </div>

        {{-- Aba: ficha de saude --}}
        @can('saude.show')
        <div class="tab-pane fade" id="as-saude">
            <div class="card-body">
                @if($ficha)
                    @if($ficha->tem_alertas)
                        <div class="alert alert-warning py-2 small">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            Aluno com alertas de saúde: verifique alergias, medicamentos e condições crônicas abaixo.
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="as-sec"><i class="fa fa-heartbeat mr-1"></i> Saúde</div>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Tipo sanguíneo</dt><dd class="col-sm-8">{{ $ficha->tipo_sanguineo_label }}</dd>
                                <dt class="col-sm-4">Alergias</dt><dd class="col-sm-8">{{ $ficha->alergias ?: '—' }}</dd>
                                <dt class="col-sm-4">Medicamentos</dt><dd class="col-sm-8">{{ $ficha->medicamentos ?: '—' }}</dd>
                                <dt class="col-sm-4">Condições crônicas</dt><dd class="col-sm-8">{{ $ficha->condicoes_cronicas ?: '—' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <div class="as-sec"><i class="fa fa-phone mr-1"></i> Contato de emergência</div>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $ficha->contato_emergencia_nome ?: '—' }}</dd>
                                <dt class="col-sm-4">Parentesco</dt><dd class="col-sm-8">{{ $ficha->contato_emergencia_parentesco ?: '—' }}</dd>
                                <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $ficha->contato_emergencia_telefone ?: '—' }}</dd>
                            </dl>
                            <div class="as-sec"><i class="fa fa-sticky-note mr-1"></i> Observações</div>
                            <p class="mb-0">{{ $ficha->observacoes ?: '—' }}</p>
                        </div>
                    </div>
                    @can('saude.update')
                        <button type="button" class="btn btn-warning btn-sm mt-3" data-toggle="modal" data-target="#modalSaude">
                            <i class="fa fa-edit"></i> Editar ficha
                        </button>
                    @endcan
                @else
                    <div class="text-center py-4">
                        <i class="fa fa-notes-medical fa-2x text-muted mb-2 d-block"></i>
                        <p class="text-muted">Ficha de saúde ainda não preenchida.</p>
                        @can('saude.update')
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalSaude">
                                <i class="fa fa-plus mr-1"></i> Preencher ficha
                            </button>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
        @endcan
    </div>
</div>

@can('saude.update')
<div class="modal fade" id="modalSaude" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('saude.update', $aluno->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <input type="hidden" name="voltar" value="aluno">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-notes-medical mr-1"></i> Ficha de Saúde — {{ $p->nome ?? 'Aluno' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                @if($errors->any() && old('voltar') === 'aluno')
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="alert alert-info py-2 small">
                    <i class="fa fa-info-circle"></i>
                    Alergias, medicamentos e condições crônicas ficam visíveis para a equipe pedagógica.
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <x-form-select name="tipo_sanguineo" label="Tipo sanguíneo" :options="['' => '—'] + $tiposSanguineos" :selected="old('tipo_sanguineo', $ficha->tipo_sanguineo ?? '')" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Alergias</label>
                        <textarea name="alergias" class="form-control" rows="3" maxlength="4000"
                            placeholder="Ex.: amendoim, poeira, medicamentos...">{{ old('alergias', $ficha->alergias ?? '') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Medicamentos em uso</label>
                        <textarea name="medicamentos" class="form-control" rows="3" maxlength="4000"
                            placeholder="Ex.: bombinha de asma, insulina...">{{ old('medicamentos', $ficha->medicamentos ?? '') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Condições crônicas</label>
                        <textarea name="condicoes_cronicas" class="form-control" rows="3" maxlength="4000"
                            placeholder="Ex.: asma, diabetes, epilepsia, TEA...">{{ old('condicoes_cronicas', $ficha->condicoes_cronicas ?? '') }}</textarea>
                    </div>
                </div>
                <hr>
                <h6 class="font-weight-bold">Contato de emergência</h6>
                <div class="row">
                    <div class="col-md-5">
                        <x-form-input name="contato_emergencia_nome" label="Nome" :value="old('contato_emergencia_nome', $ficha->contato_emergencia_nome ?? '')" maxlength="190" />
                    </div>
                    <div class="col-md-3">
                        <x-form-input name="contato_emergencia_parentesco" label="Parentesco" :value="old('contato_emergencia_parentesco', $ficha->contato_emergencia_parentesco ?? '')" maxlength="60" placeholder="Ex.: mãe, tio" />
                    </div>
                    <div class="col-md-4">
                        <x-form-input name="contato_emergencia_telefone" label="Telefone" :value="old('contato_emergencia_telefone', $ficha->contato_emergencia_telefone ?? '')" maxlength="30" placeholder="(00) 00000-0000" />
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2" maxlength="4000"
                            placeholder="Informações complementares para a equipe...">{{ old('observacoes', $ficha->observacoes ?? '') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary btn-sm">Salvar ficha</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('aluno.responsavel.store')
@include('partials.entity-picker')
<script>
(function () {
    const input = document.getElementById('responsavel_id');
    const display = document.getElementById('resp_display');

    EntityPicker.registrar('responsavel', {
        titulo: 'Buscar responsável',
        dados: @json($responsaveisPicker ?? []),
        coluna3: { campo: 'alunos', label: 'Alunos' },
        filtros: [],
        onSelect: function (d) {
            input.value = d.id;
            display.value = d.rotulo;
            display.classList.remove('is-invalid');
        },
    });
    document.getElementById('btnBuscarResp').addEventListener('click', function () {
        EntityPicker.abrir('responsavel');
    });
    input.closest('form').addEventListener('submit', function (e) {
        if (!input.value) {
            e.preventDefault();
            display.classList.add('is-invalid');
            EntityPicker.abrir('responsavel');
        }
    });
})();
</script>
@endcan
@stop

@push('js')
<script>
$(function () {
    // Abre a aba indicada pela hash da URL (ex.: retorno #as-saude apos salvar a ficha)
    var hash = location.hash;
    if (hash && $('a[data-toggle="tab"][href="' + hash + '"]').length) {
        $('a[data-toggle="tab"][href="' + hash + '"]').tab('show');
    }
    // Botao do cabecalho ativa a aba Saude sem sair da tela
    $('#btnFichaSaude').on('click', function () {
        $('a[data-toggle="tab"][href="#as-saude"]').tab('show');
    });
    @if($errors->any() && old('voltar') === 'aluno')
        // Erro de validacao do form da ficha: reabre aba e modal com os campos
        $('a[data-toggle="tab"][href="#as-saude"]').tab('show');
        $('#modalSaude').modal('show');
    @endif
});
</script>
@endpush

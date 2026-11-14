@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Documentos que a escola pede às famílias — elas enviam pelo portal" />
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<ul class="nav nav-tabs ds-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#ds-listar"><i class="fa fa-list mr-1"></i> Listar solicitações</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ds-solicitar"><i class="fa fa-paper-plane mr-1"></i> Solicitar documento</a></li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="ds-listar">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
                <div class="flex-grow-1">
                    <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
                    <button type="button" class="btn btn-outline-primary active" id="ds-btn-lista" title="Lista"><i class="fa fa-list"></i></button>
                    <button type="button" class="btn btn-outline-primary" id="ds-btn-cards" title="Cards"><i class="fa fa-th-large"></i></button>
                </div>
                <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
            </div>
            <div class="list-group list-group-flush" id="ds-lista">
                @forelse($solicitacoes as $doc)
                    @php $cores = ['pendente' => 'warning', 'enviado' => 'info', 'aceito' => 'success', 'devolvido' => 'danger']; @endphp
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <strong>{{ $doc->titulo }}</strong>
                                <span class="badge badge-{{ $cores[$doc->status] ?? 'secondary' }} ml-1">{{ $doc->status_label }}</span>
                                <div class="text-muted small">
                                    {{ $doc->aluno->pessoa->nome ?? '' }}
                                    @if($doc->prazo) · prazo {{ $doc->prazo->format('d/m/Y') }} @endif
                                    @if($doc->enviado_em) · enviado {{ $doc->enviado_em->format('d/m/Y H:i') }} @endif
                                </div>
                                @if($doc->descricao)<small>{{ $doc->descricao }}</small>@endif
                            </div>
                            <div class="d-flex align-items-center" style="gap:.4rem;">
                                @if($doc->arquivo_path)
                                    <a href="{{ route('documento_solicitacao.arquivo', $doc->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-download"></i> Ver arquivo</a>
                                @endif
                                @if($doc->status === 'enviado')
                                    <form method="POST" action="{{ route('documento_solicitacao.update', $doc->id) }}" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="aceito">
                                        <button class="btn btn-xs btn-success"><i class="fas fa-check"></i> Aceitar</button>
                                    </form>
                                    <button class="btn btn-xs btn-outline-danger" data-toggle="modal" data-target="#dev{{ $doc->id }}"><i class="fas fa-undo"></i> Devolver</button>
                                @endif
                                @if(in_array($doc->status, ['pendente', 'devolvido']))
                                    <button class="btn btn-xs btn-outline-success" data-toggle="modal" data-target="#entrega{{ $doc->id }}" title="Registrar entrega do documento com arquivo">
                                        <i class="fas fa-upload"></i> Entregar
                                    </button>
                                @endif
                            </div>
                        </div>
                        @if($doc->status === 'devolvido' && $doc->observacao)
                            <div class="alert alert-danger mt-2 mb-0 py-2 small"><strong>Motivo:</strong> {{ $doc->observacao }}</div>
                        @endif
                    </div>
                @empty
                    <div class="list-group-item text-muted text-center py-4">Nenhum documento solicitado.</div>
                @endforelse
            </div>

            <div class="card-body" id="ds-cards" style="display:none;">
                <div class="row">
                    @forelse($solicitacoes as $doc)
                        @php $cores = ['pendente' => 'warning', 'enviado' => 'info', 'aceito' => 'success', 'devolvido' => 'danger']; @endphp
                        <div class="col-md-6 col-xl-4 mb-3">
                            <div class="border rounded h-100 p-2 d-flex flex-column" style="background:#fff;">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong class="small">{{ $doc->titulo }}</strong>
                                    <span class="badge badge-{{ $cores[$doc->status] ?? 'secondary' }} ml-1">{{ $doc->status_label }}</span>
                                </div>
                                <div class="text-muted small">
                                    <i class="fa fa-user mr-1"></i>{{ $doc->aluno->pessoa->nome ?? '' }}
                                    @if($doc->prazo)<br><i class="fa fa-calendar mr-1"></i>Prazo {{ $doc->prazo->format('d/m/Y') }}@endif
                                    @if($doc->enviado_em)<br><i class="fa fa-upload mr-1"></i>{{ $doc->enviado_em->format('d/m/Y H:i') }}@endif
                                </div>
                                @if($doc->descricao)<small class="text-muted">{{ $doc->descricao }}</small>@endif
                                @if($doc->status === 'devolvido' && $doc->observacao)
                                    <div class="alert alert-danger mt-2 mb-1 py-1 small"><strong>Motivo:</strong> {{ $doc->observacao }}</div>
                                @endif
                                <div class="mt-auto pt-2 d-flex" style="gap:.35rem;">
                                    @if($doc->arquivo_path)
                                        <a href="{{ route('documento_solicitacao.arquivo', $doc->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-download"></i> Ver arquivo</a>
                                    @endif
                                    @if($doc->status === 'enviado')
                                        <form method="POST" action="{{ route('documento_solicitacao.update', $doc->id) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="aceito">
                                            <button class="btn btn-xs btn-success"><i class="fas fa-check"></i> Aceitar</button>
                                        </form>
                                        <button class="btn btn-xs btn-outline-danger" data-toggle="modal" data-target="#dev{{ $doc->id }}"><i class="fas fa-undo"></i> Devolver</button>
                                    @endif
                                    @if(in_array($doc->status, ['pendente', 'devolvido']))
                                        <button class="btn btn-xs btn-outline-success" data-toggle="modal" data-target="#entrega{{ $doc->id }}" title="Registrar entrega do documento com arquivo">
                                            <i class="fas fa-upload"></i> Entregar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted text-center py-4">Nenhum documento solicitado.</div>
                    @endforelse
                </div>
            </div>

            @foreach($solicitacoes as $doc)
                @if(in_array($doc->status, ['pendente', 'devolvido']))
                    <div class="modal fade" id="entrega{{ $doc->id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="{{ route('documento_solicitacao.update', $doc->id) }}" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="enviado">
                                <div class="modal-header"><h5 class="modal-title">Registrar entrega: {{ $doc->titulo }}</h5></div>
                                <div class="modal-body">
                                    <p class="text-muted small mb-2">O documento foi entregue presencialmente ou por outro canal? Anexe o arquivo como comprovação.</p>
                                    <label>Arquivo do documento <span class="text-danger">*</span></label>
                                    <input type="file" name="arquivo" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                                    <small class="text-muted">JPG, PNG, PDF ou Word — máx. 8 MB</small>
                                    <div class="form-group mt-2 mb-0">
                                        <label>Observação <small class="text-muted">(opcional)</small></label>
                                        <input name="observacao" class="form-control" maxlength="1000" placeholder="Ex.: entregue em mãos pelo responsável">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-upload mr-1"></i> Registrar entrega</button>
                                </div>
                            </form>
                        </div></div>
                    </div>
                @endif
                @if($doc->status === 'enviado')
                    <div class="modal fade" id="dev{{ $doc->id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="{{ route('documento_solicitacao.update', $doc->id) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="devolvido">
                                <div class="modal-header"><h5 class="modal-title">Devolver: {{ $doc->titulo }}</h5></div>
                                <div class="modal-body">
                                    <label>Motivo da devolução</label>
                                    <textarea name="observacao" class="form-control" rows="3" maxlength="1000" required placeholder="Ex.: foto ilegível, enviar frente e verso"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Devolver</button>
                                </div>
                            </form>
                        </div></div>
                    </div>
                @endif
            @endforeach

            @if($solicitacoes->hasPages())<div class="card-footer">{{ $solicitacoes->links() }}</div>@endif
        </div>
    </div>
</div>
</div>

<div class="tab-pane fade" id="ds-solicitar">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fa fa-file-alt mr-1"></i> Nova solicitação</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documento_solicitacao.store') }}" id="ds-form" novalidate>
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Aluno <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="ds_aluno_display" class="form-control" placeholder="Buscar aluno..." readonly tabindex="-1">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="btnBuscarAluno"><i class="fa fa-search mr-1"></i> Buscar</button>
                                </div>
                            </div>
                            <input type="hidden" name="aluno_id" id="aluno_id">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" name="tipo" class="form-control" maxlength="120" required placeholder="Ex.: RG do aluno, Comprovante de residência">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Detalhes <small class="text-muted">(opcional)</small></label>
                            <textarea name="descricao" class="form-control" rows="2" maxlength="2000" placeholder="Ex.: frente e verso, legível"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prazo <small class="text-muted">(opcional)</small></label>
                            <input type="date" name="prazo" class="form-control" min="{{ now()->toDateString() }}">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane mr-1"></i> Solicitar à família</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@include('partials.entity-picker')
<script>
(function () {
    // Toggle Lista/Cards com preferencia salva
    const KEY = 'doc_solicit_view';
    function setView(v) {
        const cards = v === 'cards';
        document.getElementById('ds-lista').style.display = cards ? 'none' : '';
        document.getElementById('ds-cards').style.display = cards ? '' : 'none';
        document.getElementById('ds-btn-lista').classList.toggle('active', !cards);
        document.getElementById('ds-btn-cards').classList.toggle('active', cards);
        try { localStorage.setItem(KEY, v); } catch (e) {}
    }
    document.getElementById('ds-btn-lista').addEventListener('click', function () { setView('lista'); });
    document.getElementById('ds-btn-cards').addEventListener('click', function () { setView('cards'); });
    try { setView(localStorage.getItem(KEY) || 'lista'); } catch (e) {}

    @if($errors->any())
        document.querySelector('a[href="#ds-solicitar"]').click();
    @endif
    const input = document.getElementById('aluno_id');
    const display = document.getElementById('ds_aluno_display');

    EntityPicker.registrar('aluno', {
        titulo: 'Buscar aluno',
        dados: @json($alunosPicker ?? []),
        filtros: [{ campo: 'escola_id', campoRotulo: 'escola', label: 'Escola' }],
        onSelect: function (d) {
            input.value = d.id;
            display.value = d.rotulo;
            display.classList.remove('is-invalid');
        },
    });
    document.getElementById('btnBuscarAluno').addEventListener('click', function () {
        EntityPicker.abrir('aluno');
    });
    document.getElementById('ds-form').addEventListener('submit', function (e) {
        if (!input.value) {
            e.preventDefault();
            display.classList.add('is-invalid');
            EntityPicker.abrir('aluno');
        }
    });
})();
</script>
@stop

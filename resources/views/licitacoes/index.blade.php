@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .lic-score { font-weight: 700; font-size: 1.05rem; }
        .lic-score--alto { color: #16a34a; }
        .lic-score--medio { color: #d97706; }
        .lic-score--baixo { color: #dc2626; }
        .lic-objeto { max-width: 420px; white-space: normal; }
        @media (max-width: 767.98px) {
            .lic-table thead { display: none; }
            .lic-table tr { display: block; margin-bottom: 12px; border: 1px solid #dee2e6; border-radius: 10px; }
            .lic-table td { display: flex; justify-content: space-between; align-items: center; border: none; padding: 8px 12px; }
            .lic-table td::before { content: attr(data-label); font-weight: 600; color: #6c757d; font-size: .8rem; margin-right: 12px; }
            .lic-objeto { max-width: 100%; }
        }
    </style>
@endpush

@section('content')
    <form method="GET" action="{{ route('licitacoes.index') }}" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por órgão, objeto ou nº do edital" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todos os status</option>
                @foreach ($statusOpcoes as $valor => $label)
                    <option value="{{ $valor }}" {{ request('status') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search mr-1"></i>Filtrar</button>
        </div>
    </form>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title text-bold text-sm">Licitações <span class="badge badge-primary">{{ $licitacoes->total() }}</span></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" id="btn-nova-licitacao">
                    <i class="fas fa-plus mr-1"></i> Nova Licitação
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0 lic-table">
                    <thead>
                        <tr>
                            <th>Órgão / Objeto</th>
                            <th>Edital nº</th>
                            <th>Modalidade</th>
                            <th>Abertura</th>
                            <th class="text-right">Valor estimado</th>
                            <th class="text-center">Nota estimada</th>
                            <th>Status</th>
                            <th class="text-center" style="width:120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($licitacoes as $l)
                            @php
                                $nota = $l->ultimaAnalise?->nota_tecnica_estimada;
                                $scoreClass = $nota === null ? '' : ($nota >= 70 ? 'lic-score--alto' : ($nota >= 40 ? 'lic-score--medio' : 'lic-score--baixo'));
                            @endphp
                            <tr>
                                <td data-label="Órgão / Objeto">
                                    <a href="{{ route('licitacoes.show', $l) }}" class="font-weight-bold">{{ $l->orgao }}</a>
                                    <div class="text-muted small lic-objeto">{{ Str::limit($l->objeto, 90) }}</div>
                                </td>
                                <td data-label="Edital nº" class="align-middle">{{ $l->numero_edital ?: '—' }}</td>
                                <td data-label="Modalidade" class="align-middle">{{ $l->modalidade ?: '—' }}</td>
                                <td data-label="Abertura" class="align-middle">{{ $l->data_abertura?->format('d/m/Y') ?: '—' }}</td>
                                <td data-label="Valor estimado" class="align-middle text-right">
                                    {{ $l->valor_estimado ? 'R$ '.number_format((float) $l->valor_estimado, 2, ',', '.') : '—' }}
                                </td>
                                <td data-label="Nota estimada" class="align-middle text-center">
                                    @if ($nota !== null)
                                        <span class="lic-score {{ $scoreClass }}">{{ number_format((float) $nota, 0) }}</span><span class="text-muted small">/100</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td data-label="Status" class="align-middle">
                                    <span class="badge badge-{{ ['cadastrada' => 'secondary', 'analisada' => 'info', 'participando' => 'primary', 'vencida' => 'success', 'perdida' => 'danger', 'descartada' => 'dark'][$l->status] ?? 'secondary' }}">
                                        {{ $statusOpcoes[$l->status] ?? $l->status }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('licitacoes.show', $l) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="btn btn-xs btn-warning btn-edit-licitacao"
                                        data-update-url="{{ route('licitacoes.update', $l) }}"
                                        data-orgao="{{ $l->orgao }}" data-objeto="{{ $l->objeto }}"
                                        data-numero_edital="{{ $l->numero_edital }}" data-modalidade="{{ $l->modalidade }}"
                                        data-criterio_julgamento="{{ $l->criterio_julgamento }}"
                                        data-data_abertura="{{ $l->data_abertura?->format('Y-m-d') }}"
                                        data-valor_estimado="{{ $l->valor_estimado }}" data-url_edital="{{ $l->url_edital }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form action="{{ route('licitacoes.destroy', $l) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta licitação e suas análises?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-gavel fa-2x mb-2 d-block"></i>
                                    Nenhuma licitação cadastrada. Cadastre a primeira para analisar a pontuação da empresa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($licitacoes->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">{{ $licitacoes->links() }}</div>
        @endif
    </div>

    <x-modal id="licitacaoModal" icon="fas fa-gavel" title="Nova Licitação" formId="licitacao-form" action="{{ route('licitacoes.store') }}" enctype="multipart/form-data">
        <input type="hidden" name="_edit_url" id="lic-edit-url" value="{{ old('_edit_url') }}">
        <div class="form-group">
            <label>Órgão licitante <span class="text-danger">*</span></label>
            <input type="text" name="orgao" class="form-control form-control-sm @error('orgao') is-invalid @enderror" value="{{ old('orgao') }}" required placeholder="Ex: Prefeitura Municipal de Fortaleza">
            @error('orgao')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label>Objeto <span class="text-danger">*</span></label>
            <textarea name="objeto" rows="3" class="form-control form-control-sm @error('objeto') is-invalid @enderror" required placeholder="Ex: Contratação de empresa de engenharia para execução de...">{{ old('objeto') }}</textarea>
            @error('objeto')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Nº do edital</label>
                <input type="text" name="numero_edital" class="form-control form-control-sm" value="{{ old('numero_edital') }}" placeholder="Ex: 045/2026">
            </div>
            <div class="col-md-6 form-group">
                <label>Modalidade</label>
                <select name="modalidade" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach (['Pregão Eletrônico', 'Pregão Presencial', 'Concorrência', 'Concurso', 'Diálogo Competitivo', 'Dispensa', 'Inexigibilidade', 'Leilão'] as $m)
                        <option value="{{ $m }}" {{ old('modalidade') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Critério de julgamento</label>
                <select name="criterio_julgamento" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach (['Menor preço', 'Maior desconto', 'Melhor técnica', 'Técnica e preço', 'Maior lance', 'Maior retorno econômico'] as $c)
                        <option value="{{ $c }}" {{ old('criterio_julgamento') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label>Data de abertura</label>
                <input type="date" name="data_abertura" class="form-control form-control-sm" value="{{ old('data_abertura') }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Valor estimado (R$)</label>
                <input type="number" step="0.01" min="0" name="valor_estimado" class="form-control form-control-sm" value="{{ old('valor_estimado') }}">
            </div>
            <div class="col-md-6 form-group">
                <label>Link do edital/portal</label>
                <input type="url" name="url_edital" class="form-control form-control-sm" value="{{ old('url_edital') }}" placeholder="https://...">
            </div>
        </div>
        <div class="form-group">
            <label>Arquivos do edital</label>
            <div class="custom-file">
                <input type="file" name="arquivos[]" class="custom-file-input @error('arquivos') is-invalid @enderror" id="arquivo-edital-modal" accept=".pdf,.docx,.txt" multiple>
                <label class="custom-file-label" for="arquivo-edital-modal" data-browse="Procurar">Um ou mais arquivos (PDF, DOCX, TXT)…</label>
            </div>
            @error('arquivos')<span class="text-danger small">{{ $message }}</span>@enderror
            @error('arquivos.*')<span class="text-danger small">{{ $message }}</span>@enderror
            <small class="text-muted">O texto é extraído automaticamente. Pode anexar depois, na página da licitação.</small>
        </div>
        <div class="form-group mb-0">
            <label>Texto do edital</label>
            <textarea name="texto_edital" rows="5" class="form-control form-control-sm" placeholder="Cole aqui o texto do edital — ao menos as seções de julgamento, pontuação e habilitação (necessário para a análise de IA)">{{ old('texto_edital') }}</textarea>
            <small class="text-muted">Pode colar depois, na página da licitação.</small>
        </div>
    </x-modal>
@stop

@section('js')
    <script>
        $(function() {
            var storeUrl = '{{ route('licitacoes.store') }}';

            function abrirModal() {
                $('#licitacaoModalBackdrop').addClass('show');
                $('#licitacaoModal').addClass('open');
            }

            $('.custom-file-input').on('change', function() {
                var rotulo = this.files.length === 0 ? 'Um ou mais arquivos (PDF, DOCX, TXT)…'
                    : (this.files.length === 1 ? this.files[0].name : this.files.length + ' arquivos selecionados');
                $(this).next('.custom-file-label').text(rotulo);
            });

            $('#licitacao-form').on('submit', function() {
                $(this).find('button[type=submit]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i>Salvando…');
            });

            $('#btn-nova-licitacao').on('click', function() {
                var $form = $('#licitacao-form');
                $form.attr('action', storeUrl);
                $('#licitacao-formMethod').val('POST');
                $('#licitacaoModalTitle').text('Nova Licitação');
                $('#lic-edit-url').val('');
                $form[0].reset();
                abrirModal();
            });

            $('.btn-edit-licitacao').on('click', function() {
                var d = $(this).data();
                var $form = $('#licitacao-form');
                $form.attr('action', d.updateUrl);
                $('#licitacao-formMethod').val('PUT');
                $('#licitacaoModalTitle').text('Editar Licitação');
                $('#lic-edit-url').val(d.updateUrl);
                $form[0].reset();
                ['orgao', 'objeto', 'numero_edital', 'modalidade', 'criterio_julgamento', 'data_abertura', 'valor_estimado', 'url_edital'].forEach(function(campo) {
                    $form.find('[name="' + campo + '"]').val(d[campo] || '');
                });
                abrirModal();
            });
            @if ($errors->any())
                @if (old('_edit_url'))
                    $('#licitacao-form').attr('action', @json(old('_edit_url')));
                    $('#licitacao-formMethod').val('PUT');
                    $('#licitacaoModalTitle').text('Editar Licitação');
                @endif
                abrirModal();
            @endif
        });
    </script>
@stop

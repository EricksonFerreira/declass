@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
    @if (!empty($filters))
        <div class="veiculo-filter-sticky">
            <x-filter
                :filters="$filters"
                :additionalFilters="$additionalFilters"
                :itensFiltered="$itensFiltered"
                :nomeFiltro="$nomeFiltro"
            />
        </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title text-bold text-sm"><i class="fas fa-folder-open mr-1"></i> Habilitação e documentos</h3>
            @can('documentos.store')
                <button type="button" id="btn-novo-documento" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-1"></i>Novo documento
                </button>
            @endcan
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tipo</th><th>Número</th><th>Emissor</th><th>Emissão</th>
                        <th>Validade</th><th class="text-right">Valor</th><th class="text-center">Arquivo</th><th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentos as $d)
                        <tr>
                            <td class="align-middle">
                                <div class="small font-weight-bold">{{ $d->tipo_label }}</div>
                                @if ($d->descricao)<div class="text-muted" style="font-size:.75rem">{{ \Illuminate\Support\Str::limit($d->descricao, 80) }}</div>@endif
                            </td>
                            <td class="align-middle small">{{ $d->numero ?: '—' }}</td>
                            <td class="align-middle small">{{ $d->orgao_emissor ?: '—' }}</td>
                            <td class="align-middle small">{{ $d->data_emissao?->format('d/m/Y') ?: '—' }}</td>
                            <td class="align-middle">
                                @if ($d->data_validade)
                                    {{ $d->data_validade->format('d/m/Y') }}
                                    <span class="badge badge-{{ ['vigente' => 'success', 'vencendo' => 'warning', 'vencido' => 'danger'][$d->situacao] }}">
                                        {{ $situacoes[$d->situacao] }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="align-middle text-right small">{{ $d->valor ? 'R$ '.number_format((float) $d->valor, 0, ',', '.') : '—' }}</td>
                            <td class="align-middle text-center">
                                @if ($d->arquivo_path)
                                    @can('documentos.arquivo')
                                        <a href="{{ route('documentos.arquivo', $d) }}" class="btn btn-xs btn-outline-secondary" title="Baixar arquivo"><i class="fas fa-download"></i></a>
                                    @endcan
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @can('documentos.update')
                                    <button type="button" class="btn btn-xs btn-warning btn-edit-documento"
                                        data-update-url="{{ route('documentos.update', $d) }}"
                                        data-tipo="{{ $d->tipo }}" data-numero="{{ $d->numero }}"
                                        data-orgao_emissor="{{ $d->orgao_emissor }}" data-descricao="{{ $d->descricao }}"
                                        data-valor="{{ $d->valor }}" data-data_emissao="{{ $d->data_emissao?->format('Y-m-d') }}"
                                        data-data_validade="{{ $d->data_validade?->format('Y-m-d') }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                @endcan
                                @can('documentos.destroy')
                                    <form action="{{ route('documentos.destroy', $d) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este documento e seu arquivo?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                Nenhum documento cadastrado. Cadastre CREA, certidões, atestados e certificações — eles comprovam a habilitação nas análises de licitação.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($documentos->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">{{ $documentos->links() }}</div>
        @endif
    </div>

    <x-modal id="documentoModal" icon="fas fa-folder-open" title="Novo Documento" formId="documento-form" action="{{ route('documentos.store') }}" enctype="multipart/form-data">
        <input type="hidden" name="_edit_url" id="doc-edit-url" value="{{ old('_edit_url') }}">
        <div class="form-group">
            <label>Tipo <span class="text-danger">*</span></label>
            <select name="tipo" class="form-control form-control-sm @error('tipo') is-invalid @enderror" required>
                <option value="">— selecione —</option>
                @foreach ($tipos as $v => $label)
                    <option value="{{ $v }}" {{ old('tipo') === $v ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('tipo')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Número / registro</label>
                <input type="text" name="numero" class="form-control form-control-sm" value="{{ old('numero') }}" placeholder="Ex: CREA 0601.234.567">
            </div>
            <div class="col-md-6 form-group">
                <label>Órgão emissor</label>
                <input type="text" name="orgao_emissor" class="form-control form-control-sm" value="{{ old('orgao_emissor') }}" placeholder="Ex: CREA-SP, Receita Federal">
            </div>
        </div>
        <div class="form-group">
            <label>Descrição / escopo</label>
            <textarea name="descricao" rows="2" class="form-control form-control-sm" placeholder="Para atestados: o que o documento comprova (obra, serviços, parcelas relevantes)">{{ old('descricao') }}</textarea>
        </div>
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Valor (R$)</label>
                <input type="number" step="0.01" min="0" name="valor" class="form-control form-control-sm" value="{{ old('valor') }}" placeholder="Só p/ atestados">
            </div>
            <div class="col-md-4 form-group">
                <label>Emissão</label>
                <input type="date" name="data_emissao" class="form-control form-control-sm" value="{{ old('data_emissao') }}">
            </div>
            <div class="col-md-4 form-group">
                <label>Validade</label>
                <input type="date" name="data_validade" class="form-control form-control-sm" value="{{ old('data_validade') }}">
            </div>
        </div>
        <div class="form-group mb-0">
            <label>Arquivo</label>
            <div class="custom-file">
                <input type="file" name="arquivo" class="custom-file-input @error('arquivo') is-invalid @enderror" id="arquivo-documento" accept=".pdf,.jpg,.jpeg,.png,.docx">
                <label class="custom-file-label" for="arquivo-documento" data-browse="Procurar">PDF, imagem ou DOCX…</label>
            </div>
            @error('arquivo')<span class="text-danger small">{{ $message }}</span>@enderror
            <small class="text-muted">Na edição, em branco mantém o arquivo atual.</small>
        </div>
    </x-modal>
@stop

@section('js')
    <script>
        $(function() {
            var storeUrl = '{{ route('documentos.store') }}';

            function abrirModal() {
                $('#documentoModalBackdrop').addClass('show');
                $('#documentoModal').addClass('open');
            }

            $('.custom-file-input').on('change', function() {
                $(this).next('.custom-file-label').text(this.files[0] ? this.files[0].name : 'PDF, imagem ou DOCX…');
            });

            $('#documento-form').on('submit', function() {
                $(this).find('button[type=submit]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i>Salvando…');
            });

            $('#btn-novo-documento').on('click', function() {
                var $form = $('#documento-form');
                $form.attr('action', storeUrl);
                $('#documento-formMethod').val('POST');
                $('#documentoModalTitle').text('Novo Documento');
                $('#doc-edit-url').val('');
                $form[0].reset();
                abrirModal();
            });

            $('.btn-edit-documento').on('click', function() {
                var d = $(this).data();
                var $form = $('#documento-form');
                $form.attr('action', d.updateUrl);
                $('#documento-formMethod').val('PUT');
                $('#documentoModalTitle').text('Editar Documento');
                $('#doc-edit-url').val(d.updateUrl);
                $form[0].reset();
                ['tipo', 'numero', 'orgao_emissor', 'descricao', 'valor', 'data_emissao', 'data_validade'].forEach(function(campo) {
                    $form.find('[name="' + campo + '"]').val(d[campo] || '');
                });
                abrirModal();
            });

            @if ($errors->any())
                @if (old('_edit_url'))
                    $('#documento-form').attr('action', @json(old('_edit_url')));
                    $('#documento-formMethod').val('PUT');
                    $('#documentoModalTitle').text('Editar Documento');
                @endif
                abrirModal();
            @endif
        });
    </script>
@stop

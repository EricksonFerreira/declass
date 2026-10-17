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

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $projetos->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="{{ route($nomeFiltro . '.setPerPage') }}" method="POST" class="mb-0">
                            @csrf
                            <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                        </form>
                        @include('partials.ux-view-toggle', ['viewKey' => 'projetos_index_view'])
                        @can('operacao.projetos.store')
                            <button type="button" class="btn btn-sm btn-primary" id="btn-create-projeto">
                                <i class="fas fa-plus mr-1"></i> Novo Projeto
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        @forelse ($projetos as $projeto)
                            <article class="ux-card">
                                <div class="ux-card__accent" style="background: {{ $projeto->cor ?? '#1e293b' }}"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar" style="background: {{ $projeto->cor ?? '#334155' }}"><i class="fas fa-project-diagram"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title">
                                            {{ $projeto->nome }}
                                            @if ($projeto->ano || $projeto->codigo)
                                                <span class="badge badge-secondary ml-1" style="font-weight:600;" title="{{ $projeto->identificacao_completa }}">{{ $projeto->ano ? $projeto->ano.'.'.str_pad((string) $projeto->numero, 3, '0', STR_PAD_LEFT) : '' }}{{ $projeto->ano && $projeto->codigo ? '-' : '' }}{{ $projeto->codigo }}</span>
                                            @endif
                                        </div>
                                        <div class="ux-card__sub">{{ $projeto->cliente->nome ?? 'Sem cliente' }}</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--{{ $projeto->ativo ? 'green' : 'red' }}">
                                            {{ $projeto->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        @if ($projeto->responsavel)
                                            <span class="ux-card__badge ux-card__badge--blue" title="Responsável pelo projeto"><i class="fas fa-user-tie mr-1"></i>{{ $projeto->responsavel->nome }}</span>
                                        @endif
                                        @if (! $projeto->publico)
                                            <span class="ux-card__badge ux-card__badge--gray" title="Projeto privado: visível apenas para administradores, o responsável e os membros"><i class="fas fa-lock mr-1"></i>Privado</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-money-bill-wave mr-1"></i>Faturamento</span>
                                        <strong class="ux-card__meta-value">
                                            {{ $projeto->valor_faturado > 0 ? 'R$ ' . number_format($projeto->valor_faturado, 2, ',', '.') : '—' }}
                                        </strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="far fa-calendar-check mr-1"></i>Entrega prevista</span>
                                        <strong class="ux-card__meta-value">
                                            {{ $projeto->data_entrega_previsao ? $projeto->data_entrega_previsao->format('d/m/Y') : 'Sem planejamento' }}
                                        </strong>
                                    </div>
                                    <div class="ux-card__meta-row ux-card__meta-row--full">
                                        <span class="ux-card__meta-label"><i class="fas fa-align-left mr-1"></i>Descrição</span>
                                        <strong class="ux-card__meta-value">{{ $projeto->descricao ?: '—' }}</strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <a href="{{ route('operacao.projetos.show', $projeto->id) }}" class="btn btn-xs btn-primary">
                                        <i class="fas fa-eye mr-1"></i>Detalhes
                                    </a>
                                    @if ($projeto->podeSerGerenciadoPor(auth()->user()))
                                        <button type="button" class="btn btn-xs btn-warning"
                                            onclick="editProjeto({{ json_encode($projeto) }}, {{ json_encode($projeto->users->pluck('id')->values()) }})">
                                            <i class="fa fa-edit mr-1"></i>Editar
                                        </button>
                                    @endif
                                    @can('operacao.projetos.destroy')
                                        <form action="{{ route('operacao.projetos.destroy', $projeto->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Deseja realmente excluir este projeto? Todos os dados vinculados serão removidos.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                        </form>
                                    @endcan
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                Nenhum projeto cadastrado.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">Cor</th>
                                <th>Nome</th>
                                <th>Cliente</th>
                                <th>Faturamento</th>
                                <th>Planejamento de Entrega</th>
                                <th class="text-center" style="width: 80px;">Status</th>
                                <th style="width: 240px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projetos as $projeto)
                                <tr>
                                    <td class="text-center align-middle">
                                        <span class="badge text-white" style="background-color: {{ $projeto->cor ?? '#6c757d' }}; width: 24px; height: 24px; display: inline-block; border-radius: 50%; border: 1px solid #dee2e6;">&nbsp;</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold">
                                            <a href="{{ route('operacao.projetos.show', $projeto->id) }}" class="text-dark font-weight-bold" style="text-decoration: none;">{{ $projeto->nome }}</a>
                                            @if (! $projeto->publico)
                                                <span class="badge badge-secondary ml-1" title="Projeto privado: visível apenas para administradores, o responsável e os membros"><i class="fas fa-lock mr-1"></i>Privado</span>
                                            @endif
                                            @if ($projeto->ano || $projeto->codigo)
                                                <small class="text-muted d-block" title="{{ $projeto->identificacao_completa }}">{{ $projeto->ano ? $projeto->ano.'.'.str_pad((string) $projeto->numero, 3, '0', STR_PAD_LEFT).' - ' : '' }}{{ $projeto->codigo ? 'CC '.$projeto->codigo : '' }}</small>
                                            @endif
                                        </div>
                                        @if($projeto->descricao)
                                            <small class="text-muted d-block text-truncate" style="max-width: 250px;" title="{{ $projeto->descricao }}">
                                                {{ $projeto->descricao }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="align-middle">{{ $projeto->cliente->nome ?? '-' }}</td>
                                    <td class="align-middle">
                                        @if($projeto->valor_faturado > 0)
                                            <div><strong>R$ {{ number_format($projeto->valor_faturado, 2, ',', '.') }}</strong></div>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($projeto->data_entrega_previsao)
                                            <div class="font-weight-bold text-sm text-primary">
                                                <i class="far fa-calendar-check mr-1"></i> {{ $projeto->data_entrega_previsao->format('d/m/Y') }}
                                            </div>
                                            <div class="text-xs">
                                                <span>Restam: <strong>{{ $projeto->dias_uteis_planejamento }} dias úteis</strong></span><br>
                                                <span>Horas Planejadas: <strong>{{ number_format($projeto->horas_planejadas, 1, ',', '.') }} h</strong> ({{ number_format($projeto->horas_diarias_planejamento ?? 8, 1) }}h/dia)</span>
                                                @if($projeto->valor_hora > 0)
                                                    <br><span class="text-secondary font-weight-bold">Valor da Hora: R$ {{ number_format($projeto->valor_hora, 2, ',', '.') }}</span>
                                                    <br><span class="text-dark font-weight-bold">Custo Previsto: R$ {{ number_format($projeto->custo_planejado, 2, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted text-xs">Sem planejamento</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $projeto->ativo ? 'success' : 'danger' }}">
                                            {{ $projeto->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center" style="white-space: nowrap;">
                                        <a href="{{ route('operacao.projetos.show', $projeto->id) }}" class="btn btn-xs btn-primary mr-1">
                                            <i class="fas fa-eye"></i> Detalhes
                                        </a>
                                        @if ($projeto->podeSerGerenciadoPor(auth()->user()))
                                            <button type="button" class="btn btn-xs btn-warning mr-1"
                                                onclick="editProjeto({{ json_encode($projeto) }}, {{ json_encode($projeto->users->pluck('id')->values()) }})">
                                                <i class="fa fa-edit"></i> Editar
                                            </button>
                                        @endif
                                        @can('operacao.projetos.destroy')
                                            <form action="{{ route('operacao.projetos.destroy', $projeto->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Deseja realmente excluir este projeto? Todos os dados vinculados serão removidos.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                        Nenhum projeto cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
                @if($projetos->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $projetos->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <x-modal id="projetoDrawer" icon="fas fa-project-diagram" title="Novo Projeto" formId="projeto-form" action="{{ route('operacao.projetos.store') }}">
        <input type="hidden" name="id" id="projeto_id" value="{{ old('id') }}">
        <div class="form-row">
            <div class="col-12 form-group">
                <label for="nome">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" id="nome" class="form-control form-control-sm @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required placeholder="Ex: Novo Site XPAND">
                @error('nome')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="form-row">
            <div class="col-4 form-group">
                <label for="ano">Ano</label>
                <input type="number" name="ano" id="ano" class="form-control form-control-sm @error('ano') is-invalid @enderror" value="{{ old('ano') }}" placeholder="2025" min="2000" max="2100">
                @error('ano')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-4 form-group">
                <label for="numero">Número</label>
                <input type="number" name="numero" id="numero" class="form-control form-control-sm @error('numero') is-invalid @enderror" value="{{ old('numero') }}" placeholder="33" min="1">
                <small class="text-muted">Vazio = próximo do ano</small>
                @error('numero')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-4 form-group">
                <label for="codigo">Código (CC)</label>
                <input type="text" name="codigo" id="codigo" class="form-control form-control-sm @error('codigo') is-invalid @enderror" value="{{ old('codigo') }}" placeholder="105.01" maxlength="50">
                @error('codigo')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="clockify_cliente_id">Cliente</label>
            <select name="clockify_cliente_id" id="clockify_cliente_id" class="form-control form-control-sm select2">
                <option value="">Nenhum Cliente</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected((string) old('clockify_cliente_id') === (string) $cliente->id)>{{ $cliente->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="responsavel_id">Responsável</label>
            <select name="responsavel_id" id="responsavel_id" class="form-control form-control-sm">
                <option value="">Sem responsável</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ (string) old('responsavel_id') === (string) $usuario->id ? 'selected' : '' }}>{{ $usuario->nome }}</option>
                @endforeach
            </select>
            <small class="text-muted">O responsável pode editar este projeto e acompanhar seu consumo.</small>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="2" class="form-control form-control-sm" placeholder="Resumo do projeto...">{{ old('descricao') }}</textarea>
        </div>
        <div class="form-row">
            <div class="col-6 form-group">
                <label for="valor_faturado">Valor Faturado (R$)</label>
                <input type="text" name="valor_faturado" id="valor_faturado" class="form-control form-control-sm money-mask" value="{{ old('valor_faturado') }}" placeholder="Ex: 15.000,00">
            </div>
            <div class="col-6 form-group">
                <label for="valor_hora">Valor por Hora (R$)</label>
                <input type="text" name="valor_hora" id="valor_hora" class="form-control form-control-sm money-mask" value="{{ old('valor_hora') }}" placeholder="Ex: 150,00">
            </div>
        </div>
        <div class="form-group">
            <label for="cor">Cor do Projeto</label>
            <input type="color" name="cor" id="cor" class="form-control form-control-sm" style="padding: 2px; height: 31px;" value="{{ old('cor', '#0284c7') }}">
        </div>
        <div class="form-group">
            <label for="publico">Visibilidade</label>
            <select name="publico" id="publico" class="form-control form-control-sm">
                <option value="1" @selected(old('publico', '1') === '1')>Público</option>
                <option value="0" @selected(old('publico') === '0')>Privado</option>
            </select>
            <small class="text-muted d-block mt-1">
                <strong>Público:</strong> todos da empresa veem o projeto e podem apontar horas nele.<br>
                <strong>Privado:</strong> apenas administradores, o responsável e os membros abaixo veem o projeto e podem apontar horas.
            </small>
        </div>
        <div class="form-group">
            <label>Membros do Projeto</label>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px; border-radius: 4px;">
                @foreach ($usuarios as $usuario)
                    <div class="custom-control custom-checkbox mb-1">
                        <input class="custom-control-input membro-checkbox" type="checkbox" name="membros[]" id="membro-{{ $usuario->id }}" value="{{ $usuario->id }}"
                            {{ in_array($usuario->id, (array) old('membros', [])) ? 'checked' : '' }}>
                        <label for="membro-{{ $usuario->id }}" class="custom-control-label font-weight-normal text-sm">{{ $usuario->nome }}</label>
                    </div>
                @endforeach
            </div>
            <small class="text-muted">Membros sempre veem o projeto e podem apontar horas — obrigatório quando a visibilidade é Privado.</small>
        </div>
        <div class="form-group" id="status-group" style="display: none;">
            <div class="form-row">
                <div class="col-6">
                    <label for="ativo">Registro</label>
                    <select name="ativo" id="ativo" class="form-control form-control-sm">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
                <div class="col-6">
                    <label for="status">Status do projeto</label>
                    <select name="status" id="status" class="form-control form-control-sm">
                        @foreach (\App\Models\ClockifyProjeto::STATUS_LABELS as $valor => $label)
                            <option value="{{ $valor }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </x-modal>
@stop

@section('js')
    <script>
        $(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }

            if ($.fn.inputmask) {
                $('.money-mask').inputmask('currency', {
                    radixPoint: ',',
                    groupSeparator: '.',
                    allowMinus: false,
                    prefix: 'R$ ',
                    digits: 2,
                    autoGroup: true,
                    rightAlign: false,
                    unmaskAsNumber: true,
                    removeMaskOnSubmit: true
                });
            }

            $('#btn-create-projeto').on('click', function() {
                $('#projetoDrawerTitle').text('Novo Projeto');
                $('#projeto-form').attr('action', '{{ route('operacao.projetos.store') }}');
                $('#projeto-formMethod').val('POST');
                $('#projeto-form')[0].reset();
                $('#projeto_id').val('');
                $('#projeto-form #clockify_cliente_id').val('').trigger('change');
                $('.membro-checkbox').prop('checked', false);
                $('#status-group').hide();
                $('#projetoDrawerBackdrop').addClass('show');
                $('#projetoDrawer').addClass('open');
            });

            @if ($errors->any())
                @if (old('_method') === 'PUT')
                    $('#projetoDrawerTitle').text('Editar Projeto');
                    $('#projeto-form').attr('action', '{{ url('admin/projetos') }}/' + '{{ old('id') }}');
                    $('#projeto-formMethod').val('PUT');
                    $('#status-group').show();
                @else
                    $('#projetoDrawerTitle').text('Novo Projeto');
                    $('#projeto-form').attr('action', '{{ route('operacao.projetos.store') }}');
                    $('#projeto-formMethod').val('POST');
                @endif
                $('#projetoDrawerBackdrop').addClass('show');
                $('#projetoDrawer').addClass('open');
            @endif
        });

        function editProjeto(projeto, membrosIds) {
            $('#projetoDrawerTitle').text('Editar Projeto: ' + projeto.nome);
            $('#projeto-form').attr('action', '{{ url('admin/projetos') }}/' + projeto.id);
            $('#projeto-formMethod').val('PUT');
            $('#projeto_id').val(projeto.id);
            $('#projeto-form #nome').val(projeto.nome);
            $('#projeto-form #codigo').val(projeto.codigo || '');
            $('#projeto-form #ano').val(projeto.ano || '');
            $('#projeto-form #numero').val(projeto.numero || '');
            $('#projeto-form #status').val(projeto.status || 'em_andamento');
            $('#projeto-form #clockify_cliente_id').val(projeto.clockify_cliente_id).trigger('change');
            $('#projeto-form #responsavel_id').val(projeto.responsavel_id || '');
            $('#projeto-form #descricao').val(projeto.descricao);
            $('#projeto-form #valor_faturado').val(projeto.valor_faturado);
            $('#projeto-form #valor_hora').val(projeto.valor_hora);
            $('#projeto-form #cor').val(projeto.cor ?? '#0284c7');
            $('#projeto-form #publico').val(projeto.publico ? '1' : '0');
            $('#projeto-form #ativo').val(projeto.ativo ? '1' : '0');

            var ids = (membrosIds || []).map(String);
            $('.membro-checkbox').each(function() {
                $(this).prop('checked', ids.indexOf(String($(this).val())) !== -1);
            });

            $('#status-group').show();
            $('#projetoDrawerBackdrop').addClass('show');
            $('#projetoDrawer').addClass('open');
        }
    </script>
@stop

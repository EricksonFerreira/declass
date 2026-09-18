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
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $funcoes->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="{{ route($nomeFiltro . '.setPerPage') }}" method="POST" class="mb-0">
                            @csrf
                            <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                        </form>
                        @include('partials.ux-view-toggle', ['viewKey' => 'funcoes_index_view'])
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-funcao">
                            <i class="fas fa-plus mr-1"></i> Nova Função
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        @forelse ($funcoes as $funcao)
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar"><i class="fas fa-briefcase"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title">{{ $funcao->nome }}</div>
                                        <div class="ux-card__sub">{{ $funcao->users_count }} colaborador(es) vinculado(s)</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--{{ $funcao->ativo ? 'green' : 'red' }}">
                                            {{ $funcao->ativo ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-money-bill-wave mr-1"></i>Custo/Hora</span>
                                        <strong class="ux-card__meta-value">
                                            {{ $funcao->custo_hora !== null ? 'R$ ' . number_format($funcao->custo_hora, 2, ',', '.') : '—' }}
                                        </strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-users mr-1"></i>Colaboradores</span>
                                        <strong class="ux-card__meta-value">{{ $funcao->users_count }}</strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <button type="button" class="btn btn-xs btn-warning"
                                        onclick="editFuncao({{ json_encode($funcao) }})">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </button>
                                    <form action="{{ route('clockify.funcoes.destroy', $funcao->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Deseja realmente excluir esta função?{{ $funcao->users_count > 0 ? ' Os ' . $funcao->users_count . ' colaborador(es) vinculado(s) ficarão sem função.' : '' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-briefcase fa-2x mb-2 d-block"></i>
                                Nenhuma função cadastrada.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th style="width: 130px;" class="text-right">Custo/Hora</th>
                                <th style="width: 100px;" class="text-center">Colaboradores</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($funcoes as $funcao)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $funcao->nome }}</td>
                                    <td class="align-middle text-right">
                                        @if($funcao->custo_hora !== null)
                                            R$ {{ number_format($funcao->custo_hora, 2, ',', '.') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-secondary">{{ $funcao->users_count }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $funcao->ativo ? 'success' : 'danger' }}">
                                            {{ $funcao->ativo ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-xs btn-warning mr-1"
                                            onclick="editFuncao({{ json_encode($funcao) }})">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="{{ route('clockify.funcoes.destroy', $funcao->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Deseja realmente excluir esta função?{{ $funcao->users_count > 0 ? ' Os ' . $funcao->users_count . ' colaborador(es) vinculado(s) ficarão sem função.' : '' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-briefcase fa-2x mb-2 d-block"></i>
                                        Nenhuma função cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
                @if($funcoes->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $funcoes->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <x-modal id="funcaoDrawer" icon="fas fa-briefcase" title="Nova Função" formId="funcao-form" action="{{ route('clockify.funcoes.store') }}">
        <input type="hidden" name="id" id="funcao_id" value="{{ old('id') }}">
        <div class="form-group">
            <label for="nome">Nome da Função <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required placeholder="Ex: Técnico, Engenheiro Auxiliar, Coordenador...">
            @error('nome')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="custo_hora">Custo por hora (R$)</label>
            <input type="number" name="custo_hora" id="custo_hora" step="0.01" min="0" class="form-control form-control-sm @error('custo_hora') is-invalid @enderror" value="{{ old('custo_hora') }}" placeholder="0,00">
            @error('custo_hora')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="text-muted">Custo interno padrão da função. Pode ser sobrescrito por colaborador no cadastro de usuário.</small>
        </div>
        <div class="form-group" id="status-group" style="display: none;">
            <label for="ativo">Status</label>
            <select name="ativo" id="ativo" class="form-control form-control-sm">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
        </div>
    </x-modal>
@stop

@section('js')
    <script>
        $(function() {
            $('#btn-create-funcao').on('click', function() {
                $('#funcaoDrawerTitle').text('Nova Função');
                $('#funcao-form').attr('action', '{{ route('clockify.funcoes.store') }}');
                $('#funcao-formMethod').val('POST');
                $('#funcao-form')[0].reset();
                $('#funcao_id').val('');
                $('#status-group').hide();
                $('#funcaoDrawerBackdrop').addClass('show');
                $('#funcaoDrawer').addClass('open');
            });

            @if ($errors->any())
                @if (old('_method') === 'PUT')
                    $('#funcaoDrawerTitle').text('Editar Função');
                    $('#funcao-form').attr('action', '{{ url('admin/funcoes') }}/' + '{{ old('id') }}');
                    $('#funcao-formMethod').val('PUT');
                    $('#status-group').show();
                @else
                    $('#funcaoDrawerTitle').text('Nova Função');
                    $('#funcao-form').attr('action', '{{ route('clockify.funcoes.store') }}');
                    $('#funcao-formMethod').val('POST');
                @endif
                $('#funcaoDrawerBackdrop').addClass('show');
                $('#funcaoDrawer').addClass('open');
            @endif
        });

        function editFuncao(funcao) {
            $('#funcaoDrawerTitle').text('Editar Função: ' + funcao.nome);
            $('#funcao-form').attr('action', '{{ url('admin/funcoes') }}/' + funcao.id);
            $('#funcao-formMethod').val('PUT');
            $('#funcao_id').val(funcao.id);
            $('#funcao-form #nome').val(funcao.nome);
            $('#funcao-form #custo_hora').val(funcao.custo_hora);
            $('#funcao-form #ativo').val(funcao.ativo ? '1' : '0');

            $('#status-group').show();
            $('#funcaoDrawerBackdrop').addClass('show');
            $('#funcaoDrawer').addClass('open');
        }
    </script>
@stop

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
        <!-- Listagem de Folgas e Férias -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $timeoffs->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="{{ route($nomeFiltro . '.setPerPage') }}" method="POST" class="mb-0">
                            @csrf
                            <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                        </form>
                        @include('partials.ux-view-toggle', ['viewKey' => 'timeoff_index_view', 'default' => 'table'])
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-timeoff">
                            <i class="fas fa-plus mr-1"></i> Solicitar Folga ou Férias
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="table">
                    <div class="ux-cards">
                        @forelse ($timeoffs as $to)
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar"><i class="fas fa-plane"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title">{{ $to->user->nome }}</div>
                                        <div class="ux-card__sub">
                                            @switch($to->tipo)
                                                @case('ferias') Férias @break
                                                @case('medico') Médico @break
                                                @case('pessoal') Pessoal @break
                                                @default Outro
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="ux-card__badges">
                                        @switch(strtolower($to->status ?? ''))
                                            @case('aprovado') <span class="ux-card__badge ux-card__badge--green">Aprovado</span> @break
                                            @case('rejeitado') <span class="ux-card__badge ux-card__badge--red">Rejeitado</span> @break
                                            @default <span class="ux-card__badge ux-card__badge--amber">Pendente</span>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="far fa-calendar-alt mr-1"></i>Período</span>
                                        <strong class="ux-card__meta-value">{{ $to->data_inicio->format('d/m/Y') }} → {{ $to->data_fim->format('d/m/Y') }}</strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-align-left mr-1"></i>Observação</span>
                                        <strong class="ux-card__meta-value">{{ Str::limit($to->observacao, 40) ?? '—' }}</strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    @if (strtolower($to->status ?? '') === 'pendente' && auth()->user()->podeGerenciarUsuario($to->user_id))
                                        <form action="{{ route('clockify.timeoff.approve', $to->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check mr-1"></i>Aprovar</button>
                                        </form>
                                        <form action="{{ route('clockify.timeoff.reject', $to->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times mr-1"></i>Rejeitar</button>
                                        </form>
                                    @endif
                                    @if ($to->user_id === auth()->id() || auth()->user()->podeGerenciarUsuario($to->user_id))
                                        <button type="button" class="btn btn-xs btn-warning btn-edit-timeoff"
                                            data-id="{{ $to->id }}"
                                            data-user="{{ $to->user_id }}"
                                            data-tipo="{{ $to->tipo }}"
                                            data-inicio="{{ $to->data_inicio->format('Y-m-d') }}"
                                            data-fim="{{ $to->data_fim->format('Y-m-d') }}"
                                            data-obs="{{ $to->observacao }}">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </button>
                                        <form action="{{ route('clockify.timeoff.destroy', $to->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente remover esta solicitação?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                Nenhuma solicitação de folga ou férias encontrada.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive">
                    <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Tipo</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th>Observação</th>
                                <th style="width: 200px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($timeoffs as $to)
                                <tr>
                                    <td class="align-middle text-bold">{{ $to->user->nome }}</td>
                                    <td class="align-middle">
                                        @switch($to->tipo)
                                            @case('ferias') <span class="badge bg-indigo">Férias</span> @break
                                            @case('medico') <span class="badge bg-danger">Médico</span> @break
                                            @case('pessoal') <span class="badge bg-lightblue">Pessoal</span> @break
                                            @default <span class="badge bg-secondary">Outros</span>
                                        @endswitch
                                    </td>
                                    <td class="align-middle">{{ $to->data_inicio->format('d/m/Y') }}</td>
                                    <td class="align-middle">{{ $to->data_fim->format('d/m/Y') }}</td>
                                    <td class="align-middle">
                                        @switch(strtolower($to->status ?? ''))
                                            @case('aprovado') <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aprovado</span> @break
                                            @case('rejeitado') <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejeitado</span> @break
                                            @default <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendente</span>
                                        @endswitch
                                    </td>
                                    <td class="align-middle">{{ Str::limit($to->observacao, 40) ?? '-' }}</td>
                                    <td class="align-middle text-center">
                                        @if (strtolower($to->status ?? '') === 'pendente' && auth()->user()->podeGerenciarUsuario($to->user_id))
                                            <form action="{{ route('clockify.timeoff.approve', $to->id) }}" method="POST" class="d-inline mr-1">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Aprovar</button>
                                            </form>
                                            <form action="{{ route('clockify.timeoff.reject', $to->id) }}" method="POST" class="d-inline mr-1">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times"></i> Rejeitar</button>
                                            </form>
                                        @endif
                                        @if ($to->user_id === auth()->id() || auth()->user()->podeGerenciarUsuario($to->user_id))
                                            <button type="button" class="btn btn-xs btn-info mr-1 btn-edit-timeoff"
                                                data-id="{{ $to->id }}"
                                                data-user="{{ $to->user_id }}"
                                                data-tipo="{{ $to->tipo }}"
                                                data-inicio="{{ $to->data_inicio->format('Y-m-d') }}"
                                                data-fim="{{ $to->data_fim->format('Y-m-d') }}"
                                                data-obs="{{ $to->observacao }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <form action="{{ route('clockify.timeoff.destroy', $to->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente remover esta solicitação?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-default text-danger"><i class="fa fa-trash"></i> Excluir</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                        Nenhuma solicitação de folga ou férias encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
                @if($timeoffs->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $timeoffs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <x-modal id="timeoffDrawer" icon="fas fa-plane" title="Solicitar Folga ou Férias" formId="timeoff-form" action="{{ route('clockify.timeoff.store') }}">
        <input type="hidden" name="id" id="timeoff_id" value="{{ old('id') }}">
        @if ($usuarios->count() > 1)
            <div class="form-group">
                <label for="user_id">Colaborador</label>
                <select name="user_id" id="user_id" class="form-control form-control-sm" required>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected($usuario->id === auth()->id())>{{ $usuario->nome }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
        @endif

        <div class="form-group">
            <label for="tipo">Tipo de Folga <span class="text-danger">*</span></label>
            <select name="tipo" id="tipo" class="form-control form-control-sm" required>
                <option value="ferias">Férias</option>
                <option value="medico">Médico (Licença Saúde)</option>
                <option value="pessoal">Assuntos Pessoais</option>
                <option value="outro">Outro Motivo</option>
            </select>
        </div>
        <div class="form-row">
            <div class="col-md-6 form-group">
                <label for="data_inicio">Data de Início <span class="text-danger">*</span></label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-6 form-group">
                <label for="data_fim">Data de Fim <span class="text-danger">*</span></label>
                <input type="date" name="data_fim" id="data_fim" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="observacao">Observação / Justificativa</label>
            <textarea name="observacao" id="observacao" rows="3" class="form-control form-control-sm" placeholder="Opcional..."></textarea>
        </div>
    </x-modal>
@stop

@section('js')
    <script>
        $(function() {
            var updateUrlTemplate = "{{ route('clockify.timeoff.update', ['id' => '__ID__']) }}";
            var storeUrl = "{{ route('clockify.timeoff.store') }}";

            $('#btn-create-timeoff').on('click', function() {
                var form = $('#timeoff-form');
                form[0].reset();
                form.attr('action', storeUrl);
                $('#timeoff-formMethod').val('POST');
                $('#timeoff_id').val('');
                $('#timeoffDrawerTitle').text('Solicitar Folga ou Férias');
                $('#timeoffDrawerBackdrop').addClass('show');
                $('#timeoffDrawer').addClass('open');
            });

            @if ($errors->any())
                @if (old('_method') === 'PUT')
                    $('#timeoffDrawerTitle').text('Editar Solicitação');
                    $('#timeoff-form').attr('action', updateUrlTemplate.replace('__ID__', '{{ old('id') }}'));
                    $('#timeoff-formMethod').val('PUT');
                @else
                    $('#timeoffDrawerTitle').text('Solicitar Folga ou Férias');
                    $('#timeoff-form').attr('action', storeUrl);
                    $('#timeoff-formMethod').val('POST');
                @endif
                $('#timeoffDrawerBackdrop').addClass('show');
                $('#timeoffDrawer').addClass('open');
            @endif

            // Editar: abre o mesmo drawer preenchido, enviando como PUT
            $('.btn-edit-timeoff').on('click', function() {
                var d = $(this).data();
                var form = $('#timeoff-form');
                form.attr('action', updateUrlTemplate.replace('__ID__', d.id));
                $('#timeoff-formMethod').val('PUT');
                $('#timeoffDrawerTitle').text('Editar Solicitação');
                $('#timeoff_id').val(d.id);
                $('#user_id').val(d.user);
                $('#tipo').val(d.tipo);
                $('#data_inicio').val(d.inicio);
                $('#data_fim').val(d.fim);
                $('#observacao').val(d.obs || '');
                $('#timeoffDrawerBackdrop').addClass('show');
                $('#timeoffDrawer').addClass('open');
            });
        });
    </script>
@stop

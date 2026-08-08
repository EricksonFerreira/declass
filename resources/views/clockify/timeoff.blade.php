@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <!-- Listagem de Time Off -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Histórico de Folgas e Férias</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-timeoff">
                            <i class="fas fa-plus mr-1"></i> Solicitar Time Off
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
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
                                        @switch($to->status)
                                            @case('aprovado') <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aprovado</span> @break
                                            @case('rejeitado') <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejeitado</span> @break
                                            @default <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendente</span>
                                        @endswitch
                                    </td>
                                    <td class="align-middle">{{ Str::limit($to->observacao, 40) ?? '-' }}</td>
                                    <td class="align-middle text-center">
                                        @if ($to->status === 'pendente' && auth()->user()->can('gerenciar-empresa'))
                                            <form action="{{ route('clockify.timeoff.approve', $to->id) }}" method="POST" class="d-inline mr-1">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Aprovar</button>
                                            </form>
                                            <form action="{{ route('clockify.timeoff.reject', $to->id) }}" method="POST" class="d-inline mr-1">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times"></i> Rejeitar</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('clockify.timeoff.destroy', $to->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente remover esta solicitação?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-default text-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                        Nenhuma solicitação de Time Off encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($timeoffs->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $timeoffs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <x-side-drawer id="timeoffDrawer" title="Solicitar Time Off" formId="timeoff-form" action="{{ route('clockify.timeoff.store') }}">
        @if (auth()->user()->can('gerenciar-empresa'))
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
    </x-side-drawer>
@stop

@section('js')
    <script>
        $(function() {
            $('#btn-create-timeoff').on('click', function() {
                $('#timeoff-form')[0].reset();
                $('#timeoffDrawerBackdrop').addClass('show');
                $('#timeoffDrawer').addClass('open');
            });
        });
    </script>
@stop

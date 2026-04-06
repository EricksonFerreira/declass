@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h1 class="m-0 text-dark">{{ $titulo }}</h1>
        <a href="{{ route('ordem-servico.index') }}" class="btn btn-outline-secondary btn-sm">Voltar à lista</a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Dados da OS</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Número</dt>
                        <dd class="col-sm-9"><strong>{{ $ordem_servico->numero ?? '#' . $ordem_servico->id }}</strong></dd>
                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @if($ordem_servico->status === \App\Models\OrdemServico::STATUS_ABERTA)
                                <span class="badge badge-warning">Aberta</span>
                            @elseif($ordem_servico->status === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)
                                <span class="badge badge-info">Em andamento</span>
                            @elseif($ordem_servico->status === \App\Models\OrdemServico::STATUS_CONCLUIDA)
                                <span class="badge badge-success">Concluída</span>
                            @else
                                <span class="badge badge-secondary">Cancelada</span>
                            @endif
                        </dd>
                        <dt class="col-sm-3">Veículo</dt>
                        <dd class="col-sm-9">{{ $ordem_servico->veiculo->nome ?? ('#' . $ordem_servico->veiculo_id) }}</dd>
                        <dt class="col-sm-3">Reserva</dt>
                        <dd class="col-sm-9">
                            @if($ordem_servico->reserva_id)
                                <a href="{{ route('reserva.show', $ordem_servico->reserva_id) }}">#{{ $ordem_servico->reserva_id }}</a>
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-sm-3">Abertura</dt>
                        <dd class="col-sm-9">{{ $ordem_servico->usuarioAbertura->name ?? '—' }}</dd>
                        @if($ordem_servico->data_conclusao)
                            <dt class="col-sm-3">Encerramento</dt>
                            <dd class="col-sm-9">
                                {{ $ordem_servico->usuarioEncerramento->name ?? '—' }}
                                <br><small class="text-muted">{{ $ordem_servico->data_conclusao->format('d/m/Y H:i') }}</small>
                            </dd>
                        @endif
                        @if($ordem_servico->observacoes)
                            <dt class="col-sm-3">Observações</dt>
                            <dd class="col-sm-9" style="white-space:pre-wrap;">{{ $ordem_servico->observacoes }}</dd>
                        @endif
                        @if($ordem_servico->relatorio_conclusao)
                            <dt class="col-sm-3">Relatório geral</dt>
                            <dd class="col-sm-9" style="white-space:pre-wrap;">{{ $ordem_servico->relatorio_conclusao }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> Itens do serviço</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Descrição</th>
                                <th>Vínculo avaria</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordem_servico->itens as $idx => $item)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td style="max-width:280px;white-space:normal;">{{ $item->descricao_servico }}</td>
                                    <td>
                                        @if($item->veiculo_dano_id)
                                            #{{ $item->veiculo_dano_id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="max-width:320px;white-space:normal;">
                                        @if($item->resultado)
                                            {{ $item->resultado }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($ordem_servico->podeAlterarStatus())
                <div class="card card-outline card-info mb-3">
                    <div class="card-header"><h3 class="card-title text-sm">Alterar status</h3></div>
                    <div class="card-body">
                        @if($ordem_servico->status === \App\Models\OrdemServico::STATUS_ABERTA)
                            <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}" class="mb-2">
                                @csrf
                                <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_EM_ANDAMENTO }}">
                                <button type="submit" class="btn btn-info btn-block btn-sm">Marcar em andamento</button>
                            </form>
                        @endif
                        <form method="post" action="{{ route('ordem-servico.status', $ordem_servico) }}"
                              onsubmit="return confirm('Cancelar esta OS?');">
                            @csrf
                            <input type="hidden" name="status" value="{{ \App\Models\OrdemServico::STATUS_CANCELADA }}">
                            <button type="submit" class="btn btn-outline-danger btn-block btn-sm">Cancelar OS</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($ordem_servico->podeConcluir())
                <div class="card card-outline card-success">
                    <div class="card-header"><h3 class="card-title text-sm">Concluir OS</h3></div>
                    <div class="card-body">
                        <p class="text-muted small">Ao concluir, o relatório geral é obrigatório. Para itens vinculados a avarias, a correção no cadastro de danos será preenchida com o texto do resultado (ou com o relatório geral, se o campo do item estiver vazio).</p>
                        <form method="post" action="{{ route('ordem-servico.concluir', $ordem_servico) }}">
                            @csrf
                            <div class="form-group">
                                <label for="relatorio_geral">Relatório geral <span class="text-danger">*</span></label>
                                <textarea name="relatorio_geral" id="relatorio_geral" class="form-control form-control-sm" rows="4" required minlength="3"
                                          placeholder="Resumo do que foi executado">{{ old('relatorio_geral') }}</textarea>
                            </div>
                            @if($ordem_servico->itens->isNotEmpty())
                                <p class="text-sm font-weight-bold mb-1">Resultado por item (opcional)</p>
                                @foreach($ordem_servico->itens as $item)
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0" for="res_{{ $item->id }}">Item {{ $loop->iteration }}</label>
                                        <textarea name="resultados[{{ $item->id }}]" id="res_{{ $item->id }}" class="form-control form-control-sm" rows="2"
                                                  placeholder="Deixe em branco para usar o relatório geral">{{ old('resultados.'.$item->id) }}</textarea>
                                    </div>
                                @endforeach
                            @endif
                            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check"></i> Concluir e registrar correções</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

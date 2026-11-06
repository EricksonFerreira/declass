@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Cobranças mensais das matrículas" :create-url="auth()->user()->hasPermissao('mensalidade.create') ? route('mensalidade.create') : null" :create-can="'mensalidade.create'" />
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-md-3">
        <div class="card card-outline card-danger">
            <div class="card-body text-center">
                <h5 class="mb-0">{{ $totalInadimplente }}</h5>
                <small class="text-muted">cobranças vencidas</small>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header font-weight-bold">Gerar mensalidades de uma competência</div>
            <div class="card-body">
                <form method="POST" action="{{ route('mensalidade.gerar') }}" class="form-inline">
                    @csrf
                    <div class="form-group mr-2">
                        <label class="mr-1">Competência</label>
                        <input type="month" name="competencia" class="form-control" value="{{ now()->format('Y-m') }}" required>
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-1">Vencimento</label>
                        <input type="date" name="vencimento" class="form-control" required>
                    </div>
                    @can('mensalidade.gerar')
                        <button type="submit" class="btn btn-primary">Gerar</button>
                    @endcan
                </form>
                <small class="text-muted">Gera cobranças para matrículas ativas com plano de mensalidade. Competências já existentes são ignoradas.</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('mensalidade.index') }}" class="form-inline flex-wrap" style="gap:.5rem;">
            <select name="aluno_id" class="form-control form-control-sm">
                <option value="">Todos os alunos</option>
                @foreach($alunos as $id => $nome)
                    <option value="{{ $id }}" @selected(($filters['aluno_id'] ?? '') == $id)>{{ $nome }}</option>
                @endforeach
            </select>
            <select name="turma_id" class="form-control form-control-sm">
                <option value="">Todas as turmas</option>
                @foreach($turmas as $id => $nome)
                    <option value="{{ $id }}" @selected(($filters['turma_id'] ?? '') == $id)>{{ $nome }}</option>
                @endforeach
            </select>
            <input type="month" name="competencia" class="form-control form-control-sm" value="{{ $filters['competencia'] ?? '' }}">
            <select name="status" class="form-control form-control-sm">
                <option value="">Todos os status</option>
                @foreach($statuses as $valor => $label)
                    <option value="{{ $valor }}" @selected(($filters['status'] ?? '') === $valor)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            <a href="{{ route('mensalidade.index') }}" class="btn btn-sm btn-secondary">Limpar</a>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Competência</th>
                        <th>Plano</th>
                        <th class="text-right">Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mensalidades as $mensalidade)
                        @php $situacao = $mensalidade->situacao; @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $mensalidade->matricula->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $mensalidade->matricula->turma->nome ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $mensalidade->competencia)->format('m/Y') }}</td>
                            <td>{{ $mensalidade->planoMensalidade->nome ?? '—' }}</td>
                            <td class="text-right">R$ {{ number_format($mensalidade->valor, 2, ',', '.') }}</td>
                            <td>{{ $mensalidade->vencimento->format('d/m/Y') }}</td>
                            <td>
                                @php $cor = ['pendente' => 'warning', 'vencida' => 'danger', 'paga' => 'success', 'cancelada' => 'secondary'][$situacao] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ ucfirst($situacao) }}</span>
                            </td>
                            <td>
                                @if($mensalidade->status === 'paga')
                                    <small>{{ $mensalidade->data_pagamento?->format('d/m/Y') }} · {{ \App\Models\Mensalidade::FORMAS_PAGAMENTO[$mensalidade->forma_pagamento] ?? $mensalidade->forma_pagamento }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
                                @if($mensalidade->status === 'pendente')
                                    @can('mensalidade.edit')
                                        <form method="POST" action="{{ route('mensalidade.baixar', $mensalidade->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="forma_pagamento" value="pix">
                                            <button type="submit" class="btn btn-xs btn-success" title="Baixar (receber) — registra Pix"><i class="fa fa-check"></i></button>
                                        </form>
                                    @endcan
                                @endif
                                @can('mensalidade.edit')
                                    <a href="{{ route('mensalidade.edit', $mensalidade->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('mensalidade.destroy')
                                    <x-delete-button :action="route('mensalidade.destroy', $mensalidade->id)" titulo="Excluir" mensagem="Excluir a mensalidade de &quot;{{ $mensalidade->matricula->aluno->pessoa->nome ?? 'aluno' }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-hand-holding-usd fa-2x mb-2 d-block"></i>
                                Nenhuma mensalidade encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($mensalidades->hasPages())
        <div class="card-footer">
            {{ $mensalidades->links() }}
        </div>
    @endif
</div>
@stop

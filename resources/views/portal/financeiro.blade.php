@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Mensalidades dos alunos vinculados à sua conta" />
@stop

@section('content')
@foreach($alunos as $aluno)
    @php $cobrancas = $mensalidades->get($aluno->id, collect()); @endphp
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">{{ $aluno->pessoa->nome ?? $aluno->nome }}</h3>
            @php
                $emAberto = $cobrancas->where('status', 'pendente')->sum('valor');
                $vencidas = $cobrancas->filter(fn ($m) => $m->situacao === 'vencida')->count();
            @endphp
            <div>
                @if($vencidas > 0)
                    <span class="badge badge-danger">{{ $vencidas }} vencida(s)</span>
                @endif
                <span class="badge badge-warning">R$ {{ number_format($emAberto, 2, ',', '.') }} em aberto</span>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Competência</th>
                        <th>Turma</th>
                        <th>Plano</th>
                        <th class="text-right">Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobrancas as $c)
                        @php $situacao = $c->situacao; @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $c->competencia)->format('m/Y') }}</td>
                            <td>{{ $c->matricula->turma->nome ?? '—' }}</td>
                            <td>{{ $c->planoMensalidade->nome ?? '—' }}</td>
                            <td class="text-right">R$ {{ number_format($c->valor, 2, ',', '.') }}</td>
                            <td>{{ $c->vencimento->format('d/m/Y') }}</td>
                            <td>
                                @php $cor = ['pendente' => 'warning', 'vencida' => 'danger', 'paga' => 'success', 'cancelada' => 'secondary'][$situacao] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ ucfirst($situacao) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhuma mensalidade registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<div class="mt-2">
    <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
</div>
@stop

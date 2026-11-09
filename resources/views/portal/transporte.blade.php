@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Rotas e horários de transporte dos seus alunos" />
@stop

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Aluno</th>
                        <th>Escola</th>
                        <th>Rota</th>
                        <th>Horários</th>
                        <th>Parada</th>
                        <th>Veículo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alunos as $aluno)
                        @php($v = $vinculos->get($aluno->id))
                        <tr>
                            <td class="font-weight-bold">{{ $aluno->pessoa->nome ?? 'Aluno #'.$aluno->id }}</td>
                            @if($v)
                                <td>{{ $v->rota->escola->nome ?? '—' }}</td>
                                <td>{{ $v->rota->nome ?? '—' }}</td>
                                <td class="text-nowrap">
                                    Ida: {{ $v->rota->horario_ida?->format('H:i') ?? '—' }}
                                    <br>Volta: {{ $v->rota->horario_volta?->format('H:i') ?? '—' }}
                                </td>
                                <td>{{ $v->parada->nome ?? 'Ponto mais próximo' }}</td>
                                <td>{{ $v->rota->veiculo->placa ?? '—' }}</td>
                            @else
                                <td colspan="5" class="text-muted">Sem transporte escolar vinculado.</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum aluno vinculado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
    </div>
</div>
@stop

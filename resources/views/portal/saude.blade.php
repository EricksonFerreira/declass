@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Alergias, medicamentos e contatos de emergência dos seus alunos" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @forelse($alunos as $aluno)
            @php($ficha = $fichas->get($aluno->id))
            <div class="border rounded p-3 mb-3">
                <h6 class="font-weight-bold mb-2">
                    {{ $aluno->pessoa->nome ?? $aluno->nome }}
                    @if($ficha)
                        <span class="badge badge-light border ml-1">{{ $ficha->tipo_sanguineo_label }}</span>
                    @endif
                </h6>

                @if(! $ficha)
                    <small class="text-muted">Nenhuma informação de saúde cadastrada pela escola.</small>
                @else
                    <dl class="row mb-0 small">
                        <dt class="col-sm-3">Alergias</dt>
                        <dd class="col-sm-9">{{ $ficha->alergias ?: '—' }}</dd>

                        <dt class="col-sm-3">Medicamentos</dt>
                        <dd class="col-sm-9">{{ $ficha->medicamentos ?: '—' }}</dd>

                        <dt class="col-sm-3">Condições crônicas</dt>
                        <dd class="col-sm-9">{{ $ficha->condicoes_cronicas ?: '—' }}</dd>

                        <dt class="col-sm-3">Emergência</dt>
                        <dd class="col-sm-9">
                            @if($ficha->contato_emergencia_nome)
                                {{ $ficha->contato_emergencia_nome }}
                                @if($ficha->contato_emergencia_parentesco) ({{ $ficha->contato_emergencia_parentesco }}) @endif
                                — {{ $ficha->contato_emergencia_telefone }}
                            @else
                                —
                            @endif
                        </dd>

                        @if($ficha->observacoes)
                            <dt class="col-sm-3">Observações</dt>
                            <dd class="col-sm-9">{{ $ficha->observacoes }}</dd>
                        @endif
                    </dl>
                @endif
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-notes-medical fa-3x mb-3 d-block"></i>
                Nenhum aluno vinculado a esta conta.
            </div>
        @endforelse
    </div>
</div>
@stop

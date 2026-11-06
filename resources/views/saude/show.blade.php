@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Ficha de Saúde — '.($aluno->pessoa->nome ?? 'Aluno')" subtitle="Dados de saúde e emergência" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Aluno</dt>
            <dd class="col-sm-9">{{ $aluno->pessoa->nome ?? '—' }} ({{ $aluno->escola->nome ?? '—' }})</dd>

            <dt class="col-sm-3">Tipo sanguíneo</dt>
            <dd class="col-sm-9">{{ $ficha->tipoSanguineoLabel() }}</dd>

            <dt class="col-sm-3">Alergias</dt>
            <dd class="col-sm-9">{{ $ficha->alergias ?: '—' }}</dd>

            <dt class="col-sm-3">Medicamentos em uso</dt>
            <dd class="col-sm-9">{{ $ficha->medicamentos ?: '—' }}</dd>

            <dt class="col-sm-3">Condições crônicas</dt>
            <dd class="col-sm-9">{{ $ficha->condicoes_cronicas ?: '—' }}</dd>

            <dt class="col-sm-3">Contato de emergência</dt>
            <dd class="col-sm-9">
                @if($ficha->contato_emergencia_nome)
                    {{ $ficha->contato_emergencia_nome }}
                    @if($ficha->contato_emergencia_parentesco) — {{ $ficha->contato_emergencia_parentesco }}@endif
                    <br><small>{{ $ficha->contato_emergencia_telefone }}</small>
                @else
                    —
                @endif
            </dd>

            <dt class="col-sm-3">Observações</dt>
            <dd class="col-sm-9">{{ $ficha->observacoes ?: '—' }}</dd>
        </dl>
    </div>
    <div class="card-footer">
        <a href="{{ route('saude.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
        <a href="{{ route('aluno.show', $aluno->id) }}" class="btn btn-outline-secondary btn-sm">Dados do aluno</a>
        @can('saude.edit')
            <a href="{{ route('saude.edit', $aluno->id) }}" class="btn btn-warning btn-sm">Editar ficha</a>
        @endcan
    </div>
</div>
@stop

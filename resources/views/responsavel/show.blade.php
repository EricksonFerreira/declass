@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$responsavel->nome ?? 'Responsável'" subtitle="Dados do responsável e alunos vinculados" />
@stop

@section('content')
@php($p = $responsavel->pessoa)
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header font-weight-bold">Dados Pessoais</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $p->nome ?? '—' }}</dd>
                    <dt class="col-sm-4">Nome social</dt><dd class="col-sm-8">{{ $p->nome_social ?? '—' }}</dd>
                    <dt class="col-sm-4">CPF</dt><dd class="col-sm-8">{{ $p->cpf ?? '—' }}</dd>
                    <dt class="col-sm-4">RG</dt><dd class="col-sm-8">{{ $p->rg ?? '—' }}</dd>
                    <dt class="col-sm-4">Nascimento</dt><dd class="col-sm-8">{{ optional($p->data_nascimento ?? null)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Sexo</dt><dd class="col-sm-8">{{ \App\Models\Pessoa::SEXOS[$p->sexo ?? ''] ?? '—' }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $p->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $p->telefone ?? '—' }}</dd>
                    <dt class="col-sm-4">Celular</dt><dd class="col-sm-8">{{ $p->celular ?? '—' }}</dd>
                    <dt class="col-sm-4">Endereço</dt>
                    <dd class="col-sm-8">
                        {{ collect([$p->endereco ?? null, $p->cidade ?? null, $p->estado ?? null])->filter()->implode(', ') ?: '—' }}
                        @if($p->cep) <span class="text-muted">(CEP {{ $p->cep }})</span> @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Dados do Responsável</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Profissão</dt><dd class="col-sm-8">{{ $responsavel->profissao ?? '—' }}</dd>
                    <dt class="col-sm-4">Local de trabalho</dt><dd class="col-sm-8">{{ $responsavel->local_trabalho ?? '—' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($responsavel->estaAtivo())
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-secondary">Inativo</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Observações</dt><dd class="col-sm-8">{{ $responsavel->observacoes ?? '—' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('responsavel.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('responsavel.edit')
                    <a href="{{ route('responsavel.edit', $responsavel->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header font-weight-bold">Alunos vinculados ({{ $responsavel->alunos->count() }})</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($responsavel->alunos as $aluno)
                        <li class="list-group-item py-2">
                            <a href="{{ route('aluno.show', $aluno->id) }}" class="font-weight-bold">{{ $aluno->pessoa->nomeExibicao() ?? '—' }}</a>
                            <div>
                                <span class="badge badge-info">{{ \App\Models\Aluno::TIPOS_RESPONSAVEL[$aluno->pivot->tipo] ?? $aluno->pivot->tipo }}</span>
                                @if($aluno->pivot->principal)<span class="badge badge-primary">Principal</span>@endif
                                @if($aluno->pivot->pode_retirar)<span class="badge badge-success">Pode retirar</span>@endif
                                @if(!$aluno->pivot->autoriza_contato)<span class="badge badge-secondary">Sem contato</span>@endif
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">Nenhum aluno vinculado.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

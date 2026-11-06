@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Novo Empréstimo'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('emprestimo.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <x-form-select name="aluno_id" label="Aluno" :options="['' => 'Selecionar aluno'] + ($alunos ?? [])" :selected="old('aluno_id')" required />
                </div>
                <div class="col-md-6">
                    <x-form-select name="livro_id" label="Livro" :options="['' => 'Selecionar livro'] + ($livros ?? [])" :selected="old('livro_id')" required />
                    <small class="text-muted">O número entre parênteses é a quantidade de exemplares disponíveis.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="data_emprestimo" type="date" label="Data do empréstimo" :value="old('data_emprestimo', now()->toDateString())" required />
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_devolucao_prevista" type="date" label="Devolução prevista" :value="old('data_devolucao_prevista', now()->addDays(15)->toDateString())" required />
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="3" class="form-control" maxlength="1000">{{ old('observacoes') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('emprestimo.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar empréstimo</button>
            </div>
        </form>
    </div>
</div>
@stop

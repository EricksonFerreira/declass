@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($solicitacao) ? 'Editar Solicitação' : 'Nova Solicitação de Matrícula'" />
@stop

@push('css')
<style>
    .uf-card { background: #fff; border: 1px solid #e8edf5; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.05); margin-bottom: 1rem; overflow: hidden; }
    .uf-head { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.15rem; border-bottom: 1px solid #f1f5f9; background: #fbfcfe; }
    .uf-head i { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .78rem; flex-shrink: 0; }
    .uf-head .uf-t { font-size: .86rem; font-weight: 800; color: #0f172a; }
    .uf-head .uf-s { font-size: .72rem; color: #94a3b8; }
    .uf-ic--blue { background: #eff6ff; color: #3b82f6; }
    .uf-ic--green { background: #f0fdf4; color: #22c55e; }
    .uf-ic--violet { background: #f5f3ff; color: #8b5cf6; }
    .uf-ic--amber { background: #fffbeb; color: #f59e0b; }
    .uf-body { padding: 1.1rem 1.15rem .4rem; }
    .uf-body .form-control { border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .85rem; }
    .uf-body .form-control:focus { border-color: #93c5fd; box-shadow: 0 0 0 .15rem rgba(59,130,246,.12); }
    .uf-body label { font-size: .78rem; font-weight: 700; color: #475569; }
    .uf-foot { display: flex; justify-content: flex-end; gap: .5rem; padding: .4rem 0 .25rem; }
    .uf-foot .btn { border-radius: 10px; font-weight: 700; font-size: .82rem; padding: .5rem 1.4rem; }
</style>
@endpush

@section('content')
@php $s = $solicitacao ?? null; @endphp
<form method="POST" action="{{ isset($s) ? route('solicitacao_matricula.update', $s->id) : route('solicitacao_matricula.store') }}">
    @csrf
    @if(isset($s))
        @method('PUT')
    @endif

    {{-- Escola e período pretendido --}}
    <div class="uf-card">
        <div class="uf-head">
            <i class="fa fa-school uf-ic--blue"></i>
            <div>
                <div class="uf-t">Escola e período pretendido</div>
                <div class="uf-s">Para onde o aluno deseja a matrícula</div>
            </div>
        </div>
        <div class="uf-body">
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas ?? []" :selected="old('escola_id', $s->escola_id ?? '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ano_letivo_id" label="Ano letivo" :options="($anosLetivos ?? collect())->pluck('ano', 'id')->all()" :selected="old('ano_letivo_id', $s->ano_letivo_id ?? '')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="etapa_id" label="Etapa pretendida" :options="['' => '—'] + ($etapas ?? [])" :selected="old('etapa_id', $s->etapa_id ?? '')" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <x-form-select name="turma_id" label="Turma de preferência (opcional)" :options="['' => '—'] + ($turmas ?? collect())->mapWithKeys(fn ($t) => [$t->id => $t->nome.($t->escola ? ' ('.$t->escola->nome.')' : '')])->all()" :selected="old('turma_id', $s->turma_id ?? '')" />
                </div>
                <div class="col-md-6">
                    <x-form-select name="status" label="Status" :options="$statusList ?? []" :selected="old('status', $s->status ?? 'pendente')" />
                </div>
            </div>
        </div>
    </div>

    {{-- Aluno pretendido --}}
    <div class="uf-card">
        <div class="uf-head">
            <i class="fa fa-graduation-cap uf-ic--violet"></i>
            <div>
                <div class="uf-t">Aluno pretendido</div>
                <div class="uf-s">Dados informados na pré-matrícula</div>
            </div>
        </div>
        <div class="uf-body">
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome_aluno" label="Nome completo" :value="old('nome_aluno', $s->nome_aluno ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_nascimento_aluno" type="date" label="Data de nascimento" :value="old('data_nascimento_aluno', isset($s) && $s->data_nascimento_aluno ? $s->nascimento_aluno_iso : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="sexo_aluno" label="Sexo" :options="['' => '—', 'masculino' => 'Masculino', 'feminino' => 'Feminino', 'outro' => 'Outro']" :selected="old('sexo_aluno', $s->sexo_aluno ?? '')" />
                </div>
            </div>
        </div>
    </div>

    {{-- Responsável --}}
    <div class="uf-card">
        <div class="uf-head">
            <i class="fa fa-user-friends uf-ic--green"></i>
            <div>
                <div class="uf-t">Responsável</div>
                <div class="uf-s">Contato da família</div>
            </div>
        </div>
        <div class="uf-body">
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome_responsavel" label="Nome completo" :value="old('nome_responsavel', $s->nome_responsavel ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="parentesco" label="Parentesco" :options="['' => '—'] + ($parentescos ?? [])" :selected="old('parentesco', $s->parentesco ?? '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="cpf_responsavel" label="CPF" :value="old('cpf_responsavel', $s->cpf_responsavel ?? '')" maxlength="20" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="telefone_responsavel" label="Telefone / WhatsApp" :value="old('telefone_responsavel', $s->telefone_responsavel ?? '')" maxlength="30" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="email_responsavel" type="email" label="E-mail" :value="old('email_responsavel', $s->email_responsavel ?? '')" maxlength="190" />
                </div>
            </div>
        </div>
    </div>

    {{-- Observações --}}
    <div class="uf-card">
        <div class="uf-head">
            <i class="fa fa-sticky-note uf-ic--amber"></i>
            <div>
                <div class="uf-t">Observações</div>
                <div class="uf-s">Anotações internas sobre a solicitação</div>
            </div>
        </div>
        <div class="uf-body">
            <div class="row">
                <div class="col-md-12">
                    <textarea name="observacoes" class="form-control" rows="3" maxlength="4000" placeholder="Ex.: família prefere período da manhã; aluno veio de escola particular...">{{ old('observacoes', $s->observacoes ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="uf-foot">
        <a href="{{ route('solicitacao_matricula.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> Salvar</button>
    </div>
</form>
@stop

@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($mensalidade) ? 'Editar Mensalidade' : 'Nova Mensalidade'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="{{ isset($mensalidade) ? route('mensalidade.update', $mensalidade->id) : route('mensalidade.store') }}">
            @csrf
            @if(isset($mensalidade))
                @method('PUT')
            @endif

            <div class="row">
                @if(!isset($mensalidade))
                    <div class="col-md-6">
                        <x-form-select name="matricula_id" label="Matrícula" :options="$matriculas ?? []" :selected="old('matricula_id')" placeholder="Selecionar" required />
                    </div>
                @else
                    <div class="col-md-6">
                        <x-form-input name="aluno_nome" label="Aluno" :value="$mensalidade->matricula->aluno->pessoa->nome ?? ''" disabled />
                    </div>
                @endif
                <div class="col-md-3">
                    @if(isset($mensalidade))
                        <x-form-input name="competencia" label="Competência" :value="$mensalidade->competencia" disabled />
                    @else
                        <x-form-input name="competencia" type="month" label="Competência" :value="old('competencia')" required />
                    @endif
                </div>
                <div class="col-md-3">
                    <x-form-input name="valor" type="number" label="Valor (R$)" :value="old('valor', $mensalidade->valor ?? '')" required min="0.01" step="0.01" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="vencimento" type="date" label="Vencimento" :value="old('vencimento', isset($mensalidade) && $mensalidade->vencimento ? $mensalidade->vencimento->format('Y-m-d') : '')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="status" label="Status" :options="$statuses ?? []" :selected="old('status', $mensalidade->status ?? 'pendente')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_pagamento" type="date" label="Data do pagamento" :value="old('data_pagamento', isset($mensalidade) && $mensalidade->data_pagamento ? $mensalidade->data_pagamento->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="forma_pagamento" label="Forma de pagamento" :options="$formasPagamento ?? []" :selected="old('forma_pagamento', $mensalidade->forma_pagamento ?? '')" placeholder="—" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Boleto (PDF)</label>
                    <input type="file" name="boleto" accept="application/pdf" class="form-control-file">
                    <small class="text-muted">Disponibiliza o boleto para download no portal da família.</small>
                    @error('boleto')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    @if(isset($mensalidade) && $mensalidade->boleto_path)
                        <div class="mt-1">
                            <a href="{{ route('mensalidade.boleto', $mensalidade->id) }}" target="_blank">
                                <i class="fa fa-file-pdf"></i> {{ $mensalidade->boleto_nome ?? 'Ver boleto atual' }}
                            </a>
                            <label class="ml-2 mb-0 font-weight-normal">
                                <input type="checkbox" name="remover_boleto" value="1"> remover
                            </label>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="2" class="form-control">{{ old('observacoes', $mensalidade->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('mensalidade.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop

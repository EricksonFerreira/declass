@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @php
        $isEdit = isset($movimentacao) && $movimentacao;
        $dataRegistro = $isEdit && $movimentacao->data_registro
            ? \Carbon\Carbon::parse($movimentacao->data_registro)->format('Y-m-d\\TH:i')
            : now()->format('Y-m-d\\TH:i');
        $dataSaida = $isEdit && $movimentacao->data_saida
            ? \Carbon\Carbon::parse($movimentacao->data_saida)->format('Y-m-d\\TH:i')
            : null;
        $dataChegadaPrevista = $isEdit && $movimentacao->data_chegada_prevista
            ? \Carbon\Carbon::parse($movimentacao->data_chegada_prevista)->format('Y-m-d\\TH:i')
            : null;
        $dataChegadaReal = $isEdit && $movimentacao->data_chegada_real
            ? \Carbon\Carbon::parse($movimentacao->data_chegada_real)->format('Y-m-d\\TH:i')
            : null;
    @endphp

    <div class="card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <div class="col-12">
                    <div class="mb-2 text-bold">Dados da movimentação</div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select
                            name="tipo_movimentacao_id"
                            label="Tipo"
                            :options="$tiposMovimentacao ?? []"
                            placeholder="Selecione um tipo"
                            :selected="old('tipo_movimentacao_id', $movimentacao->tipo_movimentacao_id ?? null)"
                            class="form-control form-control-sm"
                            required
                        />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select
                            name="status_movimentacao_id"
                            label="Status da movimentação"
                            :options="$statusMovimentacao ?? []"
                            placeholder="Selecione um status"
                            :selected="old('status_movimentacao_id', $movimentacao->status_movimentacao_id ?? null)"
                            class="form-control form-control-sm"
                            required
                        />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select
                            name="ativo"
                            label="Status do registro"
                            :options="$status"
                            placeholder="Selecione um valor"
                            :selected="old('ativo', $movimentacao->ativo ?? 'S')"
                            class="form-control form-control-sm"
                            required
                        />
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        <x-form-select
                            name="veiculo_id"
                            label="Veículo (opcional)"
                            :options="$veiculos ?? []"
                            placeholder="Selecione um veículo"
                            :selected="old('veiculo_id', $movimentacao->veiculo_id ?? null)"
                            class="form-control form-control-sm"
                        />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input
                            type="text"
                            name="nome"
                            label="Nome (opcional)"
                            class="form-control form-control-sm"
                            value="{{ $movimentacao->nome ?? '' }}"
                        />
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <div class="mb-2 text-bold">Locais</div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-select
                            name="local_origem_id"
                            label="Origem (opcional)"
                            :options="$estacoes ?? []"
                            placeholder="Selecione a origem"
                            :selected="old('local_origem_id', $movimentacao->local_origem_id ?? null)"
                            class="form-control form-control-sm"
                        />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-select
                            name="local_destino_id"
                            label="Destino (opcional)"
                            :options="$estacoes ?? []"
                            placeholder="Selecione o destino"
                            :selected="old('local_destino_id', $movimentacao->local_destino_id ?? null)"
                            class="form-control form-control-sm"
                        />
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <div class="mb-2 text-bold">Datas</div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input
                            type="datetime-local"
                            name="data_registro"
                            label="Data de registro"
                            class="form-control form-control-sm"
                            value="{{ $dataRegistro }}"
                            required
                        />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input
                            type="datetime-local"
                            name="data_saida"
                            label="Data de saída (opcional)"
                            class="form-control form-control-sm"
                            value="{{ $dataSaida }}"
                        />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input
                            type="datetime-local"
                            name="data_chegada_prevista"
                            label="Chegada prevista (opcional)"
                            class="form-control form-control-sm"
                            value="{{ $dataChegadaPrevista }}"
                        />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input
                            type="datetime-local"
                            name="data_chegada_real"
                            label="Chegada real (opcional)"
                            class="form-control form-control-sm"
                            value="{{ $dataChegadaReal }}"
                        />
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <div class="mb-2 text-bold">Vínculos</div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-select
                            name="user_id"
                            label="Usuário"
                            :options="$usuarios ?? []"
                            placeholder="Selecione um usuário"
                            :selected="old('user_id', $movimentacao->user_id ?? null)"
                            class="form-control form-control-sm"
                            required
                        />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input
                            type="number"
                            name="reserva_id"
                            label="Reserva (opcional)"
                            class="form-control form-control-sm"
                            value="{{ $movimentacao->reserva_id ?? '' }}"
                            min="1"
                        />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input
                            type="number"
                            name="ordem_servico_id"
                            label="OS (opcional)"
                            class="form-control form-control-sm"
                            value="{{ $movimentacao->ordem_servico_id ?? '' }}"
                            min="1"
                        />
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <div class="mb-2 text-bold">Observações</div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <x-form-label label="Observações (opcional)" for="observacoes" />
                        <textarea
                            name="observacoes"
                            id="observacoes"
                            class="form-control form-control-sm @error('observacoes') is-invalid @enderror"
                            rows="4"
                        >{{ old('observacoes', $movimentacao->observacoes ?? '') }}</textarea>
                        @error('observacoes')
                            <x-form-errors name="observacoes" />
                        @enderror
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-between mt-2">
                    <a href="{{ route('movimentacao.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Salvar
                    </button>
                </div>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop

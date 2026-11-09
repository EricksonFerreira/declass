@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($usuario) ? 'Editar Usuário' : 'Novo Usuário'" subtitle="Conta de acesso do tenant" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $formRota ?? $rota ?? (isset($usuario) ? route('usuario.update', $usuario->id) : route('usuario.store')) }}">
            @csrf
            @if(($metodo ?? 'POST') !== 'POST')
                @method($metodo)
            @endif

            <h6 class="font-weight-bold">Dados pessoais</h6>
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $usuario->nome ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="email" type="email" label="E-mail" :value="old('email', $usuario->email ?? '')" required maxlength="150" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="usuario" label="Login alternativo" :value="old('usuario', $usuario->usuario ?? '')" maxlength="60" placeholder="ex.: joao.silva" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="numero_identificacao" label="CPF / documento" :value="old('numero_identificacao', $usuario->numero_identificacao ?? '')" maxlength="30" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_nascimento" type="date" label="Data de nascimento" :value="old('data_nascimento', isset($usuario) ? optional($usuario->data_nascimento)->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="sexo" label="Sexo" :options="['M' => 'Masculino', 'F' => 'Feminino', 'NI' => 'Não informado']" :selected="old('sexo', $usuario->sexo ?? '')" placeholder="Selecionar" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="cargo" label="Cargo / função" :value="old('cargo', $usuario->cargo ?? '')" maxlength="120" placeholder="ex.: Professor de Matemática" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="telefone" label="Telefone" :value="old('telefone', $usuario->telefone ?? '')" maxlength="20" placeholder="(00) 0000-0000" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="celular" label="Celular" :value="old('celular', $usuario->celular ?? '')" maxlength="20" placeholder="(00) 00000-0000" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="cidade" label="Cidade" :value="old('cidade', $usuario->cidade ?? '')" maxlength="100" />
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold">Acesso e vínculos</h6>
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="tipo_conta" label="Tipo de conta" :options="$tiposConta ?? []" :selected="old('tipo_conta', $tipoConta ?? 'colaborador')" id="tipo-conta" />
                </div>
                <div class="col-md-4" id="grupo-perfil">
                    <x-form-select name="role_id" label="Perfil de acesso" :options="$tiposUsuario ?? []" :selected="old('role_id', $usuario->role_id ?? '')" required id="perfil-acesso" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Status" :options="['1' => 'Ativo', '0' => 'Inativo']" :selected="old('ativo', isset($usuario) ? ($usuario->ativo ? '1' : '0') : '1')" />
                </div>
            </div>

            <div class="row" id="grupo-vinculo-aluno" style="display:none;">
                <div class="col-md-8">
                    <x-form-select name="vinculo_id" label="Aluno da conta" :options="$alunosOpcoes ?? []" :selected="old('vinculo_id', ($tipoConta ?? '') === 'aluno' ? $vinculoSelecionado : '')" placeholder="Selecionar aluno" id="vinculo-aluno" />
                </div>
                <div class="col-md-4 d-flex align-items-end pb-3">
                    <small class="text-muted">Acesso do próprio aluno ao Portal Familiar (boletim, tarefas, agenda).</small>
                </div>
            </div>
            <div class="row" id="grupo-vinculo-responsavel" style="display:none;">
                <div class="col-md-8">
                    <x-form-select name="vinculo_id" label="Responsável da conta" :options="$responsaveisOpcoes ?? []" :selected="old('vinculo_id', ($tipoConta ?? '') === 'responsavel' ? $vinculoSelecionado : '')" placeholder="Selecionar responsável" id="vinculo-responsavel" />
                </div>
                <div class="col-md-4 d-flex align-items-end pb-3">
                    <small class="text-muted">Acesso da família ao Portal Familiar: vê dados dos alunos vinculados, financeiro e avisos.</small>
                </div>
            </div>

            <div class="row" id="grupo-escolas">
                <div class="col-md-12">
                    <x-form-select name="escolas[]" label="Escolas / unidades" :options="$escolasOpcoes ?? []" :selected="old('escolas', $escolasSelecionadas ?? [])" multiple />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tipo = document.getElementById('tipo-conta');
        var grupoPerfil = document.getElementById('grupo-perfil');
        var grupoEscolas = document.getElementById('grupo-escolas');
        var grupoAluno = document.getElementById('grupo-vinculo-aluno');
        var grupoResp = document.getElementById('grupo-vinculo-responsavel');
        var selPerfil = document.getElementById('perfil-acesso');
        var selAluno = document.getElementById('vinculo-aluno');
        var selResp = document.getElementById('vinculo-responsavel');

        var dadosVinculo = {
            aluno: @json($vinculoDadosAluno ?? []),
            responsavel: @json($vinculoDadosResponsavel ?? [])
        };

        function aplicarTipo() {
            var t = tipo.value;
            var portal = (t === 'aluno' || t === 'responsavel');

            grupoPerfil.style.display = portal ? 'none' : '';
            if (selPerfil) selPerfil.disabled = portal;
            if (grupoEscolas) grupoEscolas.style.display = portal ? 'none' : '';

            grupoAluno.style.display = (t === 'aluno') ? '' : 'none';
            grupoResp.style.display = (t === 'responsavel') ? '' : 'none';
            selAluno.disabled = (t !== 'aluno');
            selResp.disabled = (t !== 'responsavel');
        }

        // Preenche os dados da conta com o cadastro do vinculo escolhido.
        function preencher(tipoConta, id) {
            var d = (dadosVinculo[tipoConta] || {})[id];
            if (!d) return;

            var mapa = { nome: 'nome', email: 'email', data_nascimento: 'nascimento',
                         telefone: 'telefone', celular: 'celular', numero_identificacao: 'cpf' };
            Object.keys(mapa).forEach(function (campo) {
                var input = document.querySelector('input[name="' + campo + '"]');
                if (input && d[mapa[campo]]) input.value = d[mapa[campo]];
            });
        }

        if (selAluno) selAluno.addEventListener('change', function () { preencher('aluno', selAluno.value); });
        if (selResp) selResp.addEventListener('change', function () { preencher('responsavel', selResp.value); });
        if (tipo) tipo.addEventListener('change', aplicarTipo);
        aplicarTipo();
    });
</script>
@stop

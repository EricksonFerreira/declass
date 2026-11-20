@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($usuario) ? 'Editar Usuário' : 'Novo Usuário'" subtitle="Conta de acesso do tenant" />
@stop

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
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

    .uf-pills { display: flex; gap: .45rem; flex-wrap: wrap; }
    .uf-pill { margin: 0; }
    .uf-pill input { position: absolute; opacity: 0; pointer-events: none; }
    .uf-pill span { display: inline-flex; align-items: center; gap: .35rem; padding: .45rem .95rem; border-radius: 999px; border: 1.5px solid #e2e8f0; background: #fff; font-size: .78rem; font-weight: 700; color: #64748b; cursor: pointer; transition: all .13s; }
    .uf-pill span:hover { border-color: #c7d2fe; }
    .uf-pill input:checked + span { background: #8b5cf6; border-color: #8b5cf6; color: #fff; }
    .uf-pill--blue input:checked + span { background: #3b82f6; border-color: #3b82f6; }
    .uf-pill--green input:checked + span { background: #16a34a; border-color: #16a34a; }
    .uf-pill--red input:checked + span { background: #dc2626; border-color: #dc2626; }

    .uf-hint { display: flex; align-items: flex-start; gap: .45rem; background: #f8fafc; border: 1.5px dashed #e2e8f0; border-radius: 10px; padding: .55rem .8rem; font-size: .72rem; color: #64748b; }
    .uf-hint i { color: #8b5cf6; margin-top: .1rem; }

    .uf-foot { display: flex; justify-content: flex-end; gap: .5rem; padding: .4rem 0 .25rem; }
    .uf-foot .btn { border-radius: 10px; font-weight: 700; font-size: .82rem; padding: .5rem 1.4rem; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ $formRota ?? $rota ?? (isset($usuario) ? route('usuario.update', $usuario->id) : route('usuario.store')) }}">
    @csrf
    @if(($metodo ?? 'POST') !== 'POST')
        @method($metodo)
    @endif

    {{-- Dados pessoais --}}
    <div class="uf-card">
        <div class="uf-head">
            <i class="fa fa-user uf-ic--blue"></i>
            <div>
                <div class="uf-t">Dados pessoais</div>
                <div class="uf-s">Identificação e contato do usuário</div>
            </div>
        </div>
        <div class="uf-body">
            <div class="row">
                <div class="col-md-5">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $usuario->nome ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-4">
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
        </div>
    </div>

    {{-- Acesso --}}
    <div class="uf-card">
        <div class="uf-head">
            <i class="fa fa-key uf-ic--violet"></i>
            <div>
                <div class="uf-t">Acesso</div>
                <div class="uf-s">Tipo de conta, perfil e unidades</div>
            </div>
        </div>
        <div class="uf-body">
            <div class="row">
                <div class="col-md-5">
                    <label class="d-block mb-2">Tipo de conta</label>
                    <div class="uf-pills">
                        @php $tipoSel = old('tipo_conta', $tipoConta ?? 'colaborador'); @endphp
                        @foreach($tiposConta ?? [] as $tv => $tl)
                            <label class="uf-pill">
                                <input type="radio" name="_tipo_conta_pill" value="{{ $tv }}" {{ $tipoSel === $tv ? 'checked' : '' }}>
                                <span>
                                    @if($tv === 'colaborador')<i class="fa fa-briefcase"></i>
                                    @elseif($tv === 'aluno')<i class="fa fa-graduation-cap"></i>
                                    @else<i class="fa fa-users"></i>@endif
                                    {{ $tl }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="tipo_conta" id="tipo-conta" value="{{ $tipoSel }}">
                    @error('tipo_conta')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <div class="col-md-4" id="grupo-perfil">
                    <x-form-select name="role_id" label="Perfil de acesso" :options="$tiposUsuario ?? []" :selected="old('role_id', $usuario->role_id ?? '')" required id="perfil-acesso" />
                </div>
                <div class="col-md-3">
                    <label class="d-block mb-2">Status</label>
                    @php $ativoSel = old('ativo', isset($usuario) ? ($usuario->ativo ? '1' : '0') : '1'); @endphp
                    <div class="uf-pills">
                        <label class="uf-pill uf-pill--green">
                            <input type="radio" name="_ativo_pill" value="1" {{ $ativoSel === '1' ? 'checked' : '' }}>
                            <span><i class="fa fa-check"></i> Ativo</span>
                        </label>
                        <label class="uf-pill uf-pill--red">
                            <input type="radio" name="_ativo_pill" value="0" {{ $ativoSel === '0' ? 'checked' : '' }}>
                            <span><i class="fa fa-ban"></i> Inativo</span>
                        </label>
                    </div>
                    <input type="hidden" name="ativo" id="usuario-ativo" value="{{ $ativoSel }}">
                </div>
            </div>

            <div class="row" id="grupo-vinculo-aluno" style="display:none;">
                <div class="col-md-7">
                    <x-form-select name="vinculo_id" label="Aluno da conta" :options="$alunosOpcoes ?? []" :selected="old('vinculo_id', ($tipoConta ?? '') === 'aluno' ? $vinculoSelecionado : '')" placeholder="Selecionar aluno" id="vinculo-aluno" />
                </div>
                <div class="col-md-5 d-flex align-items-end pb-3">
                    <div class="uf-hint"><i class="fa fa-info-circle"></i> Acesso do próprio aluno ao Portal Familiar (boletim, tarefas, agenda).</div>
                </div>
            </div>
            <div class="row" id="grupo-vinculo-responsavel" style="display:none;">
                <div class="col-md-7">
                    <x-form-select name="vinculo_id" label="Responsável da conta" :options="$responsaveisOpcoes ?? []" :selected="old('vinculo_id', ($tipoConta ?? '') === 'responsavel' ? $vinculoSelecionado : '')" placeholder="Selecionar responsável" id="vinculo-responsavel" />
                </div>
                <div class="col-md-5 d-flex align-items-end pb-3">
                    <div class="uf-hint"><i class="fa fa-info-circle"></i> Acesso da família ao Portal Familiar: dados dos alunos vinculados, financeiro e avisos.</div>
                </div>
            </div>

            <div class="row" id="grupo-escolas">
                <div class="col-md-12">
                    <x-form-select name="escolas[]" label="Escolas / unidades" :options="$escolasOpcoes ?? []" :selected="old('escolas', $escolasSelecionadas ?? [])" multiple class="uf-s2" />
                </div>
            </div>
        </div>
    </div>

    {{-- Disciplinas (só professor) --}}
    <div class="uf-card" id="grupo-disciplinas" style="display:none;">
        <div class="uf-head">
            <i class="fa fa-book-open uf-ic--amber"></i>
            <div>
                <div class="uf-t">Disciplinas que leciona</div>
                <div class="uf-s">Sinalizadas na atribuição de professor à turma</div>
            </div>
        </div>
        <div class="uf-body">
            <div class="row">
                <div class="col-md-6">
                    <x-form-select name="disciplinas_principais[]" label="Principais (formação / contrato)" :options="$disciplinasOpcoes ?? []" :selected="old('disciplinas_principais', $disciplinasPrincipaisIds ?? [])" multiple class="uf-s2" />
                </div>
                <div class="col-md-6">
                    <x-form-select name="disciplinas_optativas[]" label="Optativas (pode assumir)" :options="$disciplinasOpcoes ?? []" :selected="old('disciplinas_optativas', $disciplinasOptativasIds ?? [])" multiple class="uf-s2" />
                </div>
            </div>
            <div class="uf-hint mb-3"><i class="fa fa-info-circle"></i> Ao atribuir o professor a uma turma, essas disciplinas aparecem destacadas no topo do select (principais primeiro, depois optativas).</div>
        </div>
    </div>

    <div class="uf-foot">
        <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> Salvar</button>
    </div>
</form>
@stop

@section('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
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
        var grupoDisc = document.getElementById('grupo-disciplinas');
        var rolesProfessor = @json($rolesProfessor ?? []);

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
            aplicarPerfil();
        }

        // Secao de habilitacoes aparece apenas para perfil de professor.
        function aplicarPerfil() {
            if (!grupoDisc || !selPerfil) return;
            var ehProfessor = rolesProfessor.map(String).indexOf(String(selPerfil.value)) !== -1;
            grupoDisc.style.display = ehProfessor ? '' : 'none';
        }

        // Pills de tipo de conta e status gravam nos campos ocultos.
        function ligarPill(nome, alvo) {
            document.querySelectorAll('input[name="' + nome + '"]').forEach(function (p) {
                p.addEventListener('change', function () {
                    alvo.value = p.value;
                    alvo.dispatchEvent(new Event('change'));
                });
            });
        }
        ligarPill('_tipo_conta_pill', tipo);
        ligarPill('_ativo_pill', document.getElementById('usuario-ativo'));

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
        if (selPerfil) selPerfil.addEventListener('change', aplicarPerfil);
        aplicarTipo();

        // Select2 com busca nos selects multiplos e nos vinculos (listas grandes).
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('.uf-s2, #vinculo-aluno, #vinculo-responsavel').select2({
                theme: 'bootstrap4',
                placeholder: 'Digite para buscar...',
                allowClear: false,
                width: '100%'
            });
        }
    });
</script>
@stop

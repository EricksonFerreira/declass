@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger col-xl-8 col-lg-10 col-md-12">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-8 col-lg-10 col-md-12 px-0">
        <div class="pme-card shadow-sm">

            {{-- Header --}}
            <div class="pme-card__header">
                <div class="pme-card__icon">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h4 class="pme-card__title">{{ isset($permissao) ? 'Editar Permissão' : 'Nova Permissão' }}</h4>
                    <p class="pme-card__sub">
                        Permissões controlam o que cada perfil de usuário pode acessar ou fazer no sistema.
                    </p>
                </div>
            </div>

            {{-- Body --}}
            <div class="pme-card__body">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">

                    <div class="col-12 mb-3">
                        <div class="pme-section-title">
                            <i class="fas fa-info-circle mr-1"></i> Identificação
                        </div>
                    </div>

                    {{-- Nome técnico --}}
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label class="pme-label" for="name">
                                Nome técnico <span class="text-danger">*</span>
                                <span class="pme-hint">Ex: <code>reserva.index</code>, <code>veiculo.create</code></span>
                            </label>
                            <input type="text" id="name" name="name"
                                class="form-control form-control-sm pme-input"
                                value="{{ old('name', $permissao->name ?? '') }}"
                                placeholder="modulo.acao"
                                required>
                        </div>
                    </div>

                    {{-- Guard --}}
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label class="pme-label" for="guard_name">
                                Guard (política de segurança) <span class="text-danger">*</span>
                                <span class="pme-hint">Normalmente <code>web</code></span>
                            </label>
                            <input type="text" id="guard_name" name="guard_name"
                                class="form-control form-control-sm pme-input"
                                value="{{ old('guard_name', $permissao->guard_name ?? 'web') }}"
                                required>
                        </div>
                    </div>

                    {{-- Grupo --}}
                    <div class="col-12 mb-3">
                        <div class="pme-section-title">
                            <i class="fas fa-th-large mr-1"></i> Agrupamento
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label class="pme-label" for="grupo">
                                Grupo / Módulo
                                <span class="pme-hint">Usado para organizar permissões na listagem</span>
                            </label>
                            <input type="text" id="grupo" name="grupo"
                                class="form-control form-control-sm pme-input"
                                list="grupos-list"
                                value="{{ old('grupo', $permissao->grupo ?? '') }}"
                                placeholder="Ex: reserva, veiculo, usuario…">
                            <datalist id="grupos-list">
                                @foreach($grupos as $g)
                                    <option value="{{ $g }}">
                                @endforeach
                            </datalist>
                            <small class="text-muted">
                                <i class="fas fa-lightbulb text-warning mr-1"></i>
                                Se deixar em branco, o grupo será detectado automaticamente pelo prefixo do nome técnico.
                            </small>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div class="col-12 mb-3">
                        <div class="pme-section-title">
                            <i class="fas fa-align-left mr-1"></i> Descrição
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="pme-label" for="descricao">
                                O que esta permissão concede
                                <span class="pme-hint">Explique em linguagem simples o que o usuário poderá fazer</span>
                            </label>
                            <textarea id="descricao" name="descricao" rows="3"
                                class="form-control form-control-sm pme-input"
                                placeholder="Ex: Permite que o usuário visualize a lista de todas as reservas da empresa.">{{ old('descricao', $permissao->descricao ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Info box --}}
                    <div class="col-12 mb-3">
                        <div class="alert alert-light pme-info-box">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            <div>
                                <strong>Dica sobre nomes de permissão:</strong>
                                Use o padrão <code>modulo.acao</code> para que a permissão apareça agrupada corretamente.
                                Exemplos: <code>reserva.index</code>, <code>veiculo.edit</code>, <code>multa.delete</code>.
                            </div>
                        </div>
                    </div>

                    {{-- Botões --}}
                    <div class="col-12 d-flex gap-2 mt-1">
                        <button type="submit" class="btn btn-primary pme-btn-save flex-fill">
                            <i class="fas fa-save mr-1"></i> Salvar Permissão
                        </button>
                        <a href="{{ route('permissao.index') }}" class="btn btn-outline-secondary pme-btn-cancel">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar
                        </a>
                    </div>

                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.pme-card {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e5eaf2;
    background: #fff;
}
.pme-card__header {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(120deg, #f0f4ff 0%, #e8f0fe 100%);
    padding: 1.2rem 1.5rem;
}
.pme-card__icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: #4b80ff20;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #4b80ff;
    flex-shrink: 0;
}
.pme-card__title { font-size: 1.1rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; }
.pme-card__sub   { font-size: .82rem; color: #6c757d; margin: 0; }
.pme-card__body  { padding: 1.5rem; }

.pme-section-title {
    font-size: .88rem;
    font-weight: 700;
    color: #21364f;
    letter-spacing: .02em;
    padding-bottom: 6px;
    border-bottom: 2px solid #e8f0fe;
    margin-bottom: 1rem;
}
.pme-label {
    font-weight: 600;
    font-size: .84rem;
    color: #2c4a6e;
    display: block;
    margin-bottom: .35rem;
}
.pme-hint {
    font-weight: 400;
    font-size: .76rem;
    color: #9aacbf;
    margin-left: .3rem;
}
.pme-input {
    border-radius: 8px;
    border-color: #d8e1ec;
    min-height: 36px;
}
.pme-input:focus {
    border-color: #4b80ff;
    box-shadow: 0 0 0 .15rem rgba(75,128,255,.18);
}
.pme-info-box {
    border: 1px solid #d0e3ff;
    border-radius: 10px;
    background: #f4f8ff;
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    font-size: .82rem;
}
.pme-btn-save   { border-radius: 10px; font-weight: 600; }
.pme-btn-cancel { border-radius: 10px; }
.gap-2 { gap: .5rem; }
</style>
@stop

@section('js')
<script>
// Auto-fill grupo from name prefix as user types
document.getElementById('name')?.addEventListener('input', function () {
    const grupoField = document.getElementById('grupo');
    if (!grupoField || grupoField.value.trim() !== '') return; // don't overwrite if already set
    const name = this.value.trim();
    if (name.includes('.')) {
        grupoField.placeholder = name.split('.')[0];
    }
});
</script>
@stop

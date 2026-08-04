@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Itens de Manutenção Preventiva', 'url' => route('manutencao.itens.index')], ['name' => 'Criar']]" />
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card manutencao-card shadow-sm border-0">
                <div class="card-header manutencao-card__header border-0">
                    <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap:.75rem;">
                        <div>
                            <div class="manutencao-card__eyebrow">Itens de manutenção preventiva</div>
                            <h4 class="mb-1">Novo item</h4>
                            <p class="mb-0 text-muted">Cadastre um item reutilizável para os planos do sistema.</p>
                        </div>
                        <div class="manutencao-card__badge">
                            <i class="fas fa-tools mr-1"></i> Cadastro rápido
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <form method="POST" action="{{ route('manutencao.itens.store') }}" class="p-3 p-md-4">
                        @csrf

                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <div class="form-group">
                                    <label for="nome">Nome</label>
                                    <input type="text" id="nome" name="nome" class="form-control form-control-lg" value="{{ old('nome') }}" placeholder="Ex: Troca de óleo" required>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="manutencao-card__tip">
                                    <strong>Boas práticas</strong>
                                    <span>Use nomes curtos e claros para facilitar a busca em planos e relatórios.</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" class="form-control" rows="5" placeholder="Descreva o que este item cobre">{{ old('descricao') }}</textarea>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-end" style="gap:.75rem;">
                            <a href="{{ route('manutencao.itens.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">Salvar item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .manutencao-card { border-radius: 16px; overflow: hidden; }
    .manutencao-card__header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #f7fafc 0%, #eef4ff 55%, #f7fbff 100%);
        border-bottom: 1px solid #e8edf5;
    }
    .manutencao-card__eyebrow {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #5b6b7f;
        margin-bottom: .25rem;
    }
    .manutencao-card__header h4 { font-weight: 800; color: #12263a; }
    .manutencao-card__badge,
    .manutencao-card__tip {
        border-radius: 14px;
        padding: .8rem .95rem;
        background: #fff;
        border: 1px solid #dfe7f1;
        box-shadow: 0 6px 20px rgba(18, 38, 58, .04);
    }
    .manutencao-card__badge { font-size: .9rem; font-weight: 700; color: #355070; white-space: nowrap; }
    .manutencao-card__tip {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        color: #52657a;
        min-height: 100%;
    }
    .manutencao-card .form-control {
        border-radius: 12px;
        border-color: #d8e1ec;
    }
    .manutencao-card .form-control:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
</style>
@endsection

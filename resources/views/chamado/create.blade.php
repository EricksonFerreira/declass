@extends('adminlte::page')
@section('title', 'Criar chamado')
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Criar chamado" />
@stop
@section('content')
<div class="card">
    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Corrija os campos abaixo:</strong>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('chamado.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Categoria</label>
                    <select name="chamado_categoria_id" id="chamado_categoria_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('chamado_categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                    @error('chamado_categoria_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-4 form-group">
                    <label>Subcategoria</label>
                    <select name="chamado_subcategoria_id" id="chamado_subcategoria_id" class="form-control">
                        <option value="">Selecione a categoria primeiro</option>
                    </select>
                    @error('chamado_subcategoria_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-12 form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="6" required>{{ old('descricao') }}</textarea>
                    @error('descricao')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-12 form-group">
                    <label>Anexos</label>
                    <input type="file" name="anexos[]" class="form-control" multiple>
                    @error('anexos.*')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <button class="btn btn-primary">Abrir chamado</button>
        </form>
    </div>
</div>

<script>
document.getElementById('chamado_categoria_id').addEventListener('change', async function () {
    const categoriaId = this.value;
    const sub = document.getElementById('chamado_subcategoria_id');
    sub.innerHTML = '<option value="">Carregando...</option>';

    if (!categoriaId) {
        sub.innerHTML = '<option value="">Selecione a categoria primeiro</option>';
        return;
    }

    const res = await fetch(`{{ route('chamado.subcategorias') }}?categoria_id=${categoriaId}`, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    sub.innerHTML = '<option value="">Selecione</option>' + data.map(i => `<option value="${i.id}">${i.nome} (${i.prazo_horas}h)</option>`).join('');
});
</script>
@stop

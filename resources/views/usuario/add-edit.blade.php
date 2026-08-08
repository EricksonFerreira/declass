@extends('adminlte::page')

@section('title', 'Usuários')

@section('content_header')
    <h1>{{ isset($usuario) ? 'Editar Usuário' : 'Cadastrar Usuário' }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header font-weight-bold">
                {{ isset($usuario) ? 'Editar Informações do Usuário' : 'Novo Usuário' }}
            </div>
            
            <form action="{{ $rota }}" method="POST">
                @csrf
                @if(isset($metodo) && $metodo !== 'POST')
                    @method($metodo)
                @endif
                
                <div class="card-body">
                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="nome">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $usuario->nome ?? '') }}" required>
                            @error('nome')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="email">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $usuario->email ?? '') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="celular">Celular <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('celular') is-invalid @enderror" id="celular" name="celular" value="{{ old('celular', $usuario->celular ?? '') }}" placeholder="(99) 99999-9999" required>
                            @error('celular')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="cargo">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('cargo') is-invalid @enderror" id="cargo" name="cargo" value="{{ old('cargo', $usuario->cargo ?? '') }}" required>
                            @error('cargo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    @if(!isset($usuario))
                        <div class="form-row">
                            <div class="col-md-6 form-group">
                                <label for="password">Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="confirmacao_senha">Confirmar Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('confirmacao_senha') is-invalid @enderror" id="confirmacao_senha" name="confirmacao_senha" required>
                                @error('confirmacao_senha')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('usuario.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        $('#celular').mask('(00) 00000-0000');
    });
</script>
@stop

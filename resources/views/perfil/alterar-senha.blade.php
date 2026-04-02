@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="card col-md-7 p-4">
        <x-form method="PUT" action="{{ route('profile.password.update') }}" rowClass="row">
            <div class="col-12">
                <div class="alert alert-info mb-3">
                    <strong>Segurança:</strong> informe sua senha atual e defina uma nova senha forte.
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <x-form-input type="password" name="current_password" label="Senha atual" required />
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <x-form-input type="password" name="password" label="Nova senha" required />
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <x-form-input type="password" name="password_confirmation" label="Confirmar nova senha" required />
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-xs col-12">Salvar nova senha</button>
            </div>
        </x-form>
    </div>
@stop

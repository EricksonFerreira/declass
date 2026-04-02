@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="card col-md-7 p-4">
        <x-form method="PUT" action="{{ route('profile.force-password.update') }}" rowClass="row">
            <div class="col-12">
                <div class="alert alert-warning mb-3">
                    <strong>Primeiro acesso:</strong> por seguranca, defina uma nova senha antes de continuar.
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

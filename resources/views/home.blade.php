@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Acesse rapidamente as principais áreas do sistema" />
@stop

@section('css')
@include('partials.home-cards-styles')
@stop

@section('content')
@php
    // Esconde tiles de areas que o usuario nao pode acessar (evita 403 no clique).
    $secoes = app(\App\Services\MenuSecoesService::class)->secoes(auth()->user());
@endphp

@include('partials.setup-checklist')

@include('partials.menu-cards')

@can('acessar-superadmin')
    <div class="home-section">
        <div class="home-section-title">Configurações</div>
        <div class="row">
            <div class="col-6 col-md-4 col-lg-3 mb-2">
                <a href="{{ route('empresa.index') }}" class="home-card" style="--card-cor: #dc2626;">
                    <span class="home-card-icone"><i class="fas fa-building"></i></span>
                    <span class="home-card-texto">
                        <span class="home-card-nome">Empresas</span>
                        <p class="home-card-desc">Tenants cadastrados na plataforma</p>
                    </span>
                    <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-3 mb-2">
                <a href="{{ route('comandos.index') }}" class="home-card" style="--card-cor: #0f172a;">
                    <span class="home-card-icone"><i class="fas fa-terminal"></i></span>
                    <span class="home-card-texto">
                        <span class="home-card-nome">Comandos</span>
                        <p class="home-card-desc">Manutenção e comandos do sistema</p>
                    </span>
                    <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                </a>
            </div>
        </div>
    </div>
@endcan
@stop

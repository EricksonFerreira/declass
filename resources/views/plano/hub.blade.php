@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    <x-page-header-card :title="$titulo" :subtitle="$subtitulo">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0" style="border-radius:14px; cursor:pointer;"
                     onclick="window.location='{{ route('plano.index') }}'">
                    <div class="card-body">
                        <div class="d-flex align-items-center" style="gap:.75rem;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:#eef2ff;color:#3730a3;">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">Catalogo de Planos</div>
                                <div class="text-muted" style="font-size:.82rem;">Criar/editar planos e recursos</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('plano.index') }}" class="btn btn-outline-secondary btn-sm">
                                Acessar <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0" style="border-radius:14px; cursor:pointer;"
                     onclick="window.location='{{ route('assinatura.index') }}'">
                    <div class="card-body">
                        <div class="d-flex align-items-center" style="gap:.75rem;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:#ecfeff;color:#0e7490;">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">Assinaturas</div>
                                <div class="text-muted" style="font-size:.82rem;">Atribuir e acompanhar cobrancas</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('assinatura.index') }}" class="btn btn-outline-secondary btn-sm">
                                Acessar <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0" style="border-radius:14px; cursor:pointer;"
                     onclick="window.location='{{ route('assinatura.regras') }}'">
                    <div class="card-body">
                        <div class="d-flex align-items-center" style="gap:.75rem;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:#fef3c7;color:#92400e;">
                                <i class="fas fa-book"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">Regras & Politicas</div>
                                <div class="text-muted" style="font-size:.82rem;">Padrao de upgrades, downgrades e descontos</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('assinatura.regras') }}" class="btn btn-outline-secondary btn-sm">
                                Acessar <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-page-header-card>
@stop


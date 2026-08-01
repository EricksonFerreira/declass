@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('empresa.create')" />
@stop

@php
    $totalBytes = (int) \App\Models\ArquivoRastreado::query()->sum('size_bytes');
    if ($totalBytes >= 1073741824) {
        $storageFormatted = number_format($totalBytes / 1073741824, 2, ',', '.') . ' GB';
    } else {
        $storageFormatted = number_format($totalBytes / 1048576, 2, ',', '.') . ' MB';
    }
@endphp

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $storageFormatted }}</h3>
                    <p>Armazenamento total</p>
                </div>
                <div class="icon"><i class="fas fa-database"></i></div>
                <a href="#" class="small-box-footer" onclick="event.preventDefault();alert('Armazenamento total de todas as empresas somadas. O detalhamento por empresa aparece na tabela abaixo.');">
                    <i class="fas fa-chart-simple mr-1"></i> Detalhes por empresa na tabela
                </a>
            </div>
        </div>
    </div>

    @if (!empty($filters))
        <x-filter
            :filters="$filters"
            :additionalFilters="$additionalFilters"
            :itensFiltered="$itensFiltered"
            :nomeFiltro="$nomeFiltro"
        />
    @endif

    <div class="card">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h3 class="card-title mb-0 text-sm font-weight-bold">
                    Registros
                    <span class="badge badge-success ml-1">{{ $empresas->total() }}</span>
                </h3>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <form action="{{ route('empresa.setPerPage') }}" method="POST" class="mb-0">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    {{ $empresas->links() }}
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm mb-0 empresa-table" style="font-size:13px;">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Empresa</th>
                        <th>Contato</th>
                        <th>Plano</th>
                        <th>Gateway / Moeda</th>
                        <th>Armazenamento</th>
                        <th>Cores</th>
                        <th>Status</th>
                        <th class="text-center" style="width:220px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empresas as $empresa)
                        @php
                            $gateway = $empresa->gateway_pagamento ?? null;
                            $moeda = $empresa->moeda_pagamento ?? null;
                            $corPrimaria = $empresa->cor_primaria ?? null;
                            $corSecundaria = $empresa->cor_secundaria ?? null;
                            $corTerciaria = $empresa->cor_terciaria ?? null;
                        @endphp
                        <tr>
                            <td class="pl-3 align-middle">
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    @if(!empty($empresa->logo_url))
                                        <img
                                            src="{{ $empresa->logo_url }}"
                                            alt="Logo {{ $empresa->nome }}"
                                            class="empresa-logo-lista"
                                            style="background: {{ $empresa->cor_primaria ?? '#1F2937' }};"
                                        >
                                    @else
                                        <div class="empresa-logo-fallback">
                                            {{ strtoupper(mb_substr($empresa->nome ?? 'E', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-weight-bold">{{ $empresa->nome }}</div>
                                        <div class="text-muted text-xs">Slug: {{ $empresa->slug }}</div>
                                        @if(!empty($empresa->dominio))
                                            <div class="text-muted text-xs">{{ $empresa->dominio }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="align-middle">
                                <div class="text-xs">
                                    <div><strong>CNPJ:</strong> {{ $empresa->cnpj ?? '-' }}</div>
                                    <div><strong>E-mail:</strong> {{ $empresa->email ?? '-' }}</div>
                                    <div><strong>Telefone:</strong> {{ $empresa->telefone ?? '-' }}</div>
                                </div>
                            </td>

                            @php
                                $planoAtivo = $empresa->planoAtivo;
                            @endphp
                            <td class="align-middle text-xs">
                                @if($planoAtivo)
                                    <div><strong>{{ $planoAtivo->plano?->nome ?? '-' }}</strong></div>
                                    <div>
                                        <span class="badge badge-xs bg-{{ $planoAtivo->status === 'ativo' ? 'success' : ($planoAtivo->status === 'trial' ? 'info' : ($planoAtivo->status === 'inadimplente' ? 'danger' : 'warning')) }}">
                                            {{ $planoAtivo->status_badge['label'] ?? $planoAtivo->status }}
                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size:10px;">
                                        {{ $planoAtivo->periodicidade_texto }}
                                        {{ $planoAtivo->data_vencimento ? '· até ' . $planoAtivo->data_vencimento->format('d/m/Y') : '' }}
                                    </div>
                                @else
                                    <span class="text-muted font-italic">Sem plano</span>
                                @endif
                                <div style="margin-top:2px;">
                                    <a href="{{ route('assinatura.index') }}" class="text-primary" style="font-size:10px;">
                                        <i class="fas fa-external-link-alt mr-1"></i> Gerenciar assinaturas
                                    </a>
                                </div>
                            </td>

                            <td class="align-middle">
                                <div class="text-xs mb-1">
                                    <strong>Gateway:</strong>
                                    @if($gateway)
                                        {{ strtoupper(str_replace('_', ' ', $gateway)) }}
                                    @else
                                        <span class="text-muted font-italic">nao definido</span>
                                    @endif
                                </div>
                                <div class="text-xs mb-2">
                                    <strong>Moeda:</strong>
                                    @if($moeda)
                                        {{ $moeda }}
                                    @else
                                        <span class="text-muted font-italic">nao definida</span>
                                    @endif
                                </div>
                            </td>

                            <td class="align-middle text-xs">
                                @php
                                    $pct = $empresa->storage_percent;
                                    $barColor = $pct >= 90 ? '#dc2626' : ($pct >= 60 ? '#d97706' : ($pct >= 30 ? '#2563eb' : '#16a34a'));
                                    $labelColor = $pct >= 90 ? 'text-danger' : ($pct >= 60 ? 'text-warning' : 'text-success');
                                @endphp
                                <div class="{{ $labelColor }}" style="font-weight:600;font-size:11px;">
                                    <i class="fas fa-database mr-1"></i> {{ $empresa->storage_used }}
                                </div>
                                <div style="margin-top:3px;background:#e5e7eb;border-radius:4px;height:8px;width:100px;overflow:hidden;">
                                    <div style="width:{{ min($pct, 100) }}%;height:100%;background:{{ $barColor }};border-radius:4px;"></div>
                                </div>
                                <div class="text-muted" style="font-size:9px;margin-top:1px;">
                                    {{ $pct }}% do limite ({{ $empresa->planoAtivo?->plano?->nome ?? 'Sem plano' }})
                                </div>
                            </td>

                            <td class="align-middle">
                                @if($corPrimaria || $corSecundaria || $corTerciaria)
                                    <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                        @if($corPrimaria)
                                            <span class="empresa-cor-chip" title="Cor primaria">
                                                <span class="empresa-cor-dot" style="background: {{ $corPrimaria }}"></span>
                                                <span>{{ $corPrimaria }}</span>
                                            </span>
                                        @endif
                                        @if($corSecundaria)
                                            <span class="empresa-cor-chip" title="Cor secundaria">
                                                <span class="empresa-cor-dot" style="background: {{ $corSecundaria }}"></span>
                                                <span>{{ $corSecundaria }}</span>
                                            </span>
                                        @endif
                                        @if($corTerciaria)
                                            <span class="empresa-cor-chip" title="Cor terciaria">
                                                <span class="empresa-cor-dot" style="background: {{ $corTerciaria }}"></span>
                                                <span>{{ $corTerciaria }}</span>
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge badge-secondary">SEM CORES</span>
                                @endif
                            </td>

                            <td class="align-middle">
                                <span class="badge badge-{{ ($empresa->ativo ?? 'S') === 'S' ? 'success' : 'danger' }}">
                                    {{ ($empresa->ativo ?? 'S') === 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('empresa.site-editor.edit', $empresa->id) }}" class="btn btn-xs btn-info mb-1" style="background:#0ea5e9;border-color:#0ea5e9;">
                                    <i class="fa fa-paint-brush"></i> Editor Site
                                </a>
                                {{-- <a href="{{ route('empresa.site-apresentacao.edit', $empresa->id) }}" class="btn btn-xs btn-info mb-1">
                                    <i class="fa fa-globe"></i> Site
                                </a> --}}
                                <a href="{{ route('empresa.edit', $empresa->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('empresa.destroy', $empresa->id)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Nenhuma empresa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($empresas->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">
                {{ $empresas->links() }}
            </div>
        @endif
    </div>
@stop

@section('css')
<style>
    .empresa-logo-lista {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        object-fit: contain;
        padding: 2px;
    }

    .empresa-logo-fallback {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background: #1f2937;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .empresa-table .text-xs {
        font-size: 12px;
        line-height: 1.35;
    }

    .empresa-cor-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #334155;
    }

    .empresa-cor-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 1px solid rgba(15, 23, 42, .15);
        display: inline-block;
    }
</style>
@stop

@section('js')
<script></script>
@stop

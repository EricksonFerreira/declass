@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @php
        $formatCurrency = function ($value) use ($moedaEmpresa) {
            return ($moedaEmpresa === 'BRL' ? 'R$' : $moedaEmpresa) . ' ' . number_format((float) $value, 2, ',', '.');
        };

        $onlyDigits = function ($value) {
            return preg_replace('/\D+/', '', (string) $value);
        };

        $buildWaLink = function ($value, $message) use ($onlyDigits) {
            $digits = $onlyDigits($value);
            if ($digits === '') {
                return null;
            }

            if (! str_starts_with($digits, '55')) {
                $digits = '55' . $digits;
            }

            return 'https://wa.me/' . $digits . '?text=' . urlencode($message);
        };
    @endphp

    <div class="fc-page">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card fc-card h-100">
                    <div class="card-body">
                        <div class="text-muted text-xs">Clientes</div>
                        <div class="h3 mb-0">{{ number_format((int) ($totais->clientes ?? 0), 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card fc-card h-100">
                    <div class="card-body">
                        <div class="text-muted text-xs">Transações</div>
                        <div class="h3 mb-0">{{ number_format((int) ($totais->transacoes ?? 0), 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card fc-card h-100">
                    <div class="card-body">
                        <div class="text-muted text-xs">Pago</div>
                        <div class="h3 mb-0 text-success">{{ $formatCurrency($totais->pago ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card fc-card h-100">
                    <div class="card-body">
                        <div class="text-muted text-xs">Em aberto</div>
                        <div class="h3 mb-0 text-warning">{{ $formatCurrency($totais->aberto ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card fc-card fc-filter mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('financeiro.analise-clientes') }}" class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="fc-label" for="busca">Cliente / telefone</label>
                        <input type="text" name="busca" id="busca" class="form-control" value="{{ request('busca') }}" placeholder="Nome, celular ou telefone">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="fc-label" for="cliente_id">Cliente</label>
                        <select name="cliente_id" id="cliente_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ (int) request('cliente_id') === (int) $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="fc-label" for="status_cobranca">Cobrança</label>
                        <select name="status_cobranca" id="status_cobranca" class="form-control">
                            <option value="">Todos</option>
                            <option value="em_aberto" {{ request('status_cobranca') === 'em_aberto' ? 'selected' : '' }}>Em aberto</option>
                            <option value="pago" {{ request('status_cobranca') === 'pago' ? 'selected' : '' }}>Pago</option>
                            <option value="cancelado" {{ request('status_cobranca') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="fc-label" for="per_page">Por página</label>
                        <select name="per_page" id="per_page" class="form-control">
                            @foreach([10, 15, 25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="fc-label" for="data_inicio">Data inicial</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="fc-label" for="data_fim">Data final</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim') }}">
                    </div>
                    <div class="col-md-3 mb-2 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1"><i class="fas fa-filter"></i> Filtrar</button>
                        <a href="{{ route('financeiro.analise-clientes') }}" class="btn btn-outline-secondary fc-btn">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card fc-card h-100">
                    <div class="card-header">
                        <strong>Melhores clientes</strong>
                    </div>
                    <div class="card-body">
                        @forelse($melhoresClientes as $cliente)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <div class="font-weight-bold">{{ $cliente->cliente_nome }}</div>
                                    <div class="text-muted text-xs">{{ $formatCurrency($cliente->total_pago ?? 0) }} pago</div>
                                </div>
                                @if($link = $buildWaLink($cliente->celular ?? '', 'Olá ' . $cliente->cliente_nome . ', passando para avisar sobre seus lançamentos em aberto.'))
                                    <a href="{{ $link }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted">Sem dados para exibir.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card fc-card h-100">
                    <div class="card-header">
                        <strong>Clientes com mais pendências</strong>
                    </div>
                    <div class="card-body">
                        @forelse($pioresClientes as $cliente)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <div class="font-weight-bold">{{ $cliente->cliente_nome }}</div>
                                    <div class="text-muted text-xs">{{ $formatCurrency($cliente->total_pendente ?? 0) }} em aberto</div>
                                </div>
                                @if($link = $buildWaLink($cliente->celular ?? '', 'Olá ' . $cliente->cliente_nome . ', estamos entrando em contato sobre valores pendentes.'))
                                    <a href="{{ $link }}" target="_blank" rel="noopener" class="btn btn-sm btn-warning text-white">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted">Sem dados para exibir.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card fc-card h-100">
                    <div class="card-header">
                        <strong>Atalho de cobrança</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Abra o WhatsApp do cliente com uma mensagem pronta para cobrança quando houver número cadastrado.</p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Usa celular ou telefone cadastrado</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Abre em nova aba</li>
                            <li><i class="fas fa-check-circle text-success mr-1"></i> Mensagem pré-preenchida</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h3 class="fc-title">
                        <i class="fas fa-user-friends"></i>
                        <span>Clientes
                            <span class="fc-subtitle">Ranking financeiro, pendências e atalho de cobrança</span>
                        </span>
                    </h3>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover fc-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Cliente</th>
                                <th class="py-3">Pago</th>
                                <th class="py-3">Em aberto</th>
                                <th class="py-3">Qtd.</th>
                                <th class="py-3">Última mov.</th>
                                <th class="py-3 text-right px-4">Cobrança</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analise as $cliente)
                                @php
                                    $mensagem = 'Olá ' . $cliente->cliente_nome . ', estamos entrando em contato sobre valores em aberto.';
                                    $waLink = $buildWaLink($cliente->celular ?: $cliente->telefone, $mensagem);
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-weight-bold">{{ $cliente->cliente_nome }}</div>
                                        <div class="text-muted text-xs">{{ $cliente->celular ?: $cliente->telefone ?: 'Sem contato' }}</div>
                                    </td>
                                    <td class="py-3 text-success font-weight-bold">{{ $formatCurrency($cliente->total_pago ?? 0) }}</td>
                                    <td class="py-3 text-warning font-weight-bold">{{ $formatCurrency($cliente->total_pendente ?? 0) }}</td>
                                    <td class="py-3">{{ number_format((int) ($cliente->quantidade ?? 0), 0, ',', '.') }}</td>
                                    <td class="py-3 text-muted">{{ $cliente->ultima_movimentacao ? \Carbon\Carbon::parse($cliente->ultima_movimentacao)->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="py-3 text-right px-4">
                                        @if($waLink)
                                            <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                                                <i class="fab fa-whatsapp mr-1"></i> Cobrar
                                            </a>
                                        @else
                                            <span class="text-muted">Sem número</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Nenhum cliente encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($analise->hasPages())
                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
                        {{ $analise->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('financeiro.fluxo_caixa._styles')
@stop

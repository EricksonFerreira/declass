<?php

namespace App\Actions\Reserva\Checkin\Contrato\Exibir;

use App\Models\Reserva;
use App\Models\TermoConsentimento;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExibirContratoCheckinReservaComumAction
{
    public function __construct(
        private PdfService $pdfService,
    ) {}

    public function gerarPdf(Reserva $reserva): string
    {
        $data = $this->montarDados($reserva);

        return $this->pdfService->gerarConteudo(
            view: 'pdf.contratos.checkin.comum',
            data: $data,
        );
    }

    public function montarDados(Reserva $reserva, ?string $tipoContrato = null): array
    {
        $moedaSimbolo = match (strtoupper($reserva->empresa?->moeda_pagamento ?? 'BRL')) {
            'USD' => '$',
            'EUR' => '€',
            default => 'R$',
        };
        $money = fn ($v) => $moedaSimbolo.' '.number_format((float) ($v ?? 0), 2, ',', '.');
        $dateTime = fn ($v) => $v ? Carbon::parse($v)->format('d/m/Y H:i') : '-';
        $dateOnly = fn ($v) => $v ? Carbon::parse($v)->format('d/m/Y') : '-';
        $safe = fn ($v, $d = '-') => filled($v) ? $v : $d;

        // Check if there is a linked Motorista App reservation to swap placeholder data
        $motoristaApp = \App\Models\ReservaMotoristaApp::with(['user', 'estacaoRetirada', 'planoMotoristaApp', 'pagamentos'])
            ->where('documentos_json->checkin->reserva_operacional_id', (int) $reserva->id)
            ->orWhere('id', (int) $reserva->id)
            ->first();

        if ($motoristaApp) {
            $usuario = $motoristaApp->user;
            $dias = (int) ($motoristaApp->quantidade_dias ?? 7);
            $tarifaBase = (float) ($motoristaApp->valor_plano ?? 0);
            $valorTarifaBase = $tarifaBase;
            $valorOpcionais = (float) ($motoristaApp->valor_opcionais ?? 0);
            $taxaColeta = 0.0;
            $taxaEntrega = 0.0;
            $percentualDesconto = 0.0;
            $subtotal = $valorTarifaBase + $valorOpcionais;
            $valorDesconto = 0.0;
            $valorTotal = round($subtotal, 2);
            $totalPago = (float) $motoristaApp->pagamentos->where('status', 'pago')->sum('valor');
            $saldoPendente = max(0, $valorTotal - $totalPago);

            $opcionais = [];
            $opcionaisSelecionados = is_array($motoristaApp->opcionais_json ?? null) ? $motoristaApp->opcionais_json : [];
            foreach ($opcionaisSelecionados as $item) {
                $opcionais[] = [
                    'nome' => $item['nome'] ?? 'Opcional',
                    'quantidade' => $item['quantidade'] ?? 1,
                    'valor' => (float) ($item['preco_total'] ?? 0),
                    'valor_total' => $money($item['preco_total'] ?? 0),
                    'tipo_cobranca' => $item['tipo_cobranca'],
                    'preco_unitario' => $money($item['preco_unitario'] ?? 0),
                ];
            }
        } else {
            $usuario = $reserva->usuario;
            $dias = (int) ($reserva->quantidade_dias ?? 0);
            $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
            $valorTarifaBase = (float) ($reserva->valor_total_tarifa_base ?? 0) ?: $tarifaBase * $dias;
            $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
            if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
                $valorOpcionais = (float) $reserva->opcionais->sum(fn ($o) => (float) ($o->pivot->preco_total ?? 0));
            }
            $taxaColeta = (float) ($reserva->taxa_coleta ?? 0);
            $taxaEntrega = (float) ($reserva->taxa_entrega ?? 0);
            $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
            $subtotal = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
            $valorDesconto = $subtotal * ($percentualDesconto / 100);
            $valorTotal = max(0, $subtotal - $valorDesconto);
            $totalPago = (float) $reserva->transacoes
                ->whereIn('status', ['succeeded', 'approved', 'paid', 'confirmed', 'concluido'])
                ->sum('valor');
            $saldoPendente = max(0, $valorTotal - $totalPago);

            $opcionais = $reserva->opcionais->map(fn ($o) => [
                'nome' => $o->nome,
                'tipo_cobranca' => $o->tipo_cobranca,
                'quantidade' => $o->pivot->quantidade,
                'valor' => (float) $o->pivot->preco_total,
                'valor_total' => $money($o->pivot->preco_total),
                'preco_unitario' => $o->preco_por_dia ? $money($o->preco_por_dia) : $money($o->preco_integral),
            ])->toArray();
        }

        $logoData = null;
        $logoPath = is_string($reserva->empresa?->logo_path ?? null) ? trim($reserva->empresa->logo_path) : '';
        if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            $mime = mime_content_type(Storage::disk('public')->path($logoPath));
            $logoData = 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($logoPath));
        }

        $danos = $reserva->veiculoDanos->sortByDesc('id')->map(fn ($d) => [
            'id' => $d->id,
            'localizacao' => strtoupper((string) ($d->local_dano ?? '-')),
            'categoria' => $d->dano?->categoria?->nome ?? '-',
            'localizacao_problema' => $safe($d->localizacao_problema),
            'tipo_dano' => $safe($d->tipo_dano),
            'observacoes' => $safe($d->observacoes),
            'quilometragem' => $d->quilometragem_veiculo ? number_format((float) $d->quilometragem_veiculo, 0, ',', '.').' km' : '-',
            'custo_estimado' => $money($d->custo_estimado),
            'status' => $d->correcao_data ? 'Corrigido' : 'Pendente',
            'correcao' => $safe($d->correcao_descricao),
            'correcao_data' => $d->correcao_data ? $dateTime($d->correcao_data) : '-',
            'correcao_usuario' => $d->usuarioCorrecao?->nome ?? '-',
            'registrado_em' => $dateTime($d->created_at),
            'imagens' => $d->imagem->map(function ($img) {
                if (! $img->caminho_imagem) {
                    return null;
                }
                $p = $img->caminho_imagem;
                if (! Storage::disk('public')->exists($p)) {
                    return null;
                }
                $m = mime_content_type(Storage::disk('public')->path($p));

                return 'data:'.$m.';base64,'.base64_encode(Storage::disk('public')->get($p));
            })->filter()->values()->toArray(),
        ])->values()->toArray();

        return [
            'documento' => [
                'codigo_interno' => 'ARCS-'.date('y'),
                'atcud' => 'JJNXXNNC-'.date('y'),
                'data_emissao' => now()->format('d/m/Y H:i:s'),
                'data_assinatura' => $reserva->contratoCheckin->data_assinatura ?? null,
                'empresa' => [
                    'nome' => $reserva->empresa->nome ?? config('app.empresa.nome'),
                    'endereco' => config('app.empresa.endereco'),
                    'site' => config('app.empresa.site'),
                    'instagram' => config('app.empresa.instagram'),
                    'logo_url' => $logoData,
                ],
            ],
            'contrato' => [
                'numero' => $motoristaApp ? ($motoristaApp->codigoReserva?->codigo ?? 'MAP-'.str_pad($motoristaApp->id, 6, '0', STR_PAD_LEFT)) : '#'.str_pad($reserva->id, 5, '0', STR_PAD_LEFT),
                'titulo' => 'CONTRATO DE ALUGUEL',
                'subtitulo' => $motoristaApp ? 'CONDIÇÕES PARTICULARES DO CONTRATO DE MOTORISTA APP' : 'CONDIÇÕES PARTICULARES DO CONTRATO DE ALUGUEL',
                'periodicidade_label' => $motoristaApp ? 'Semanal' : 'Ciclo',
                'periodicidade' => $motoristaApp ? 'Semanal' : 'Ciclo',
                'assinatura' => $reserva->contratoCheckin->assinatura ?? null,
                'data_assinatura' => $reserva->contratoCheckin ? ($reserva->contratoCheckin->data_assinatura ? Carbon::parse($reserva->contratoCheckin->data_assinatura)->format('d/m/Y H:i') : null) : null,
            ],
            'cliente' => [
                'nome' => $usuario->nome ?? $usuario->name ?? '-',
                'nif' => $usuario->nif ?? '-',
                'email' => $usuario->email ?? '-',
                'telefone' => $usuario->telefone ?? '-',
                'passaporte' => $safe($usuario->passaporte ?? null),
                'data_nascimento' => $dateOnly($usuario->data_nascimento ?? null),
                'endereco' => $usuario->endereco ?? '-',
                'pais' => $usuario->pais ?? '-',
                'carta_conducao' => [
                    'pais' => $usuario->pais_emissao ?? '-',
                    'numero' => $usuario->numero_carta_conducao ?? '-',
                    'data_emissao' => $dateOnly($usuario->data_emissao_carta_conducao ?? null),
                    'validade' => $dateOnly($usuario->data_validade_carta_conducao ?? null),
                ],
            ],
            'veiculo' => [
                'matricula' => $safe($reserva->contratoCheckin->veiculo?->placa_licenca),
                'marca' => $safe($reserva->contratoCheckin->marca?->nome ?? $reserva->contratoCheckin->veiculo?->marca?->nome),
                'modelo' => $safe($reserva->contratoCheckin->modelo?->nome ?? $reserva->contratoCheckin->veiculo?->modelo?->nome),
                'grupo' => $safe($reserva->contratoCheckin->veiculo?->grupoFrota?->nome ?? $reserva->grupoFrota?->nome),
                'quilometros' => $reserva->contratoCheckin->quilometragem_atual,
                'combustivel' => $safe($reserva->contratoCheckin->nivel_combustivel),
                'taxas' => ['combustivel_em_falta' => $money(0), 'taxa_reabastecimento' => $money(0)],
                'franquia' => $money(0),
                'kms_permitidos' => $reserva->kms_permitidos ?? $reserva->contratoCheckin->veiculo?->limite_kms ?? 'ilimitado',
                'nome' => $safe($reserva->contratoCheckin->veiculo?->nome),
            ],
            'reserva' => [
                'id' => $motoristaApp ? $motoristaApp->id : $reserva->id,
                'status' => $motoristaApp ? $safe($motoristaApp->status) : $safe($reserva->statusReserva?->nome),
                'origem' => $motoristaApp ? 'Motorista App' : $safe($reserva->label_origem_cadastro),
                'criada_em' => $motoristaApp ? $dateTime($motoristaApp->created_at) : $dateTime($reserva->created_at),
                'pagamento_efetuado' => $motoristaApp ? $motoristaApp->pagamento_efetuado : (($reserva->pagamento_efetuado ?? 'N') === 'S'),
                'grupo_reservado' => $motoristaApp ? $safe($motoristaApp->planoMotoristaApp?->nome) : $safe($reserva->grupoFrota?->nome),
                'modelo_reservado' => $motoristaApp ? $safe($motoristaApp->planoMotoristaApp?->nome) : $safe($reserva->modelos?->nome),
                'observacoes' => $motoristaApp ? $safe($motoristaApp->observacoes) : $safe($reserva->observacoes),
                'estacao_retirada' => $motoristaApp ? $safe($motoristaApp->estacaoRetirada?->nome) : $safe($reserva->estacaoRetirada?->nome),
                'data_inicio' => $motoristaApp ? $dateTime($motoristaApp->data_inicio) : $dateTime($reserva->data_inicio),
                'data_fim' => $motoristaApp ? $dateTime($motoristaApp->data_fim) : $dateTime($reserva->data_fim),
                'plano_nome' => $motoristaApp ? $safe($motoristaApp->planoMotoristaApp?->nome) : $safe($reserva->grupoFrota?->nome),
                'quantidade_dias' => $dias,
                'alugado_em' => [
                    'local' => $motoristaApp ? $safe($motoristaApp->estacaoRetirada?->nome) : $safe($reserva->estacaoRetirada?->nome),
                    'data' => $motoristaApp ? $dateOnly($motoristaApp->data_inicio) : $dateOnly($reserva->data_inicio),
                    'hora' => $motoristaApp ? ($motoristaApp->data_inicio ? Carbon::parse($motoristaApp->data_inicio)->format('H:i') : '-') : ($reserva->data_inicio ? Carbon::parse($reserva->data_inicio)->format('H:i') : '-'),
                ],
                'devolver_em' => [
                    'local' => $motoristaApp ? $safe($motoristaApp->estacaoRetirada?->nome) : $safe($reserva->estacaoDevolucao?->nome),
                    'data' => $motoristaApp ? $dateOnly($motoristaApp->data_fim) : $dateOnly($reserva->data_fim),
                    'hora' => $motoristaApp ? ($motoristaApp->data_fim ? Carbon::parse($motoristaApp->data_fim)->format('H:i') : '-') : ($reserva->data_fim ? Carbon::parse($reserva->data_fim)->format('H:i') : '-'),
                ],
            ],
            'valores' => [
                'valor_periodo' => $motoristaApp ? $tarifaBase : $valorTarifaBase,
                'valor_opcionais' => $motoristaApp ? $valorOpcionais : $valorOpcionais,
                'valor_total' => $motoristaApp ? $valorTotal : $valorTotal,
                'caucao' => $motoristaApp ? (float) ($motoristaApp->valor_caucao ?? $motoristaApp->planoMotoristaApp?->calcao ?? 0) : 0,
            ],
            'caucao' => [
                'valor' => $motoristaApp ? (float) ($motoristaApp->valor_caucao ?? $motoristaApp->planoMotoristaApp?->calcao ?? 0) : 0,
                'status' => 'Em Aberto / Pré-autorizado',
                'metodo' => 'Cartão de Crédito / Presencial',
            ],
            'faturacao' => [
                'dias_unidades' => $dias,
                'tarifa_diaria' => $money($tarifaBase),
                'valor_tarifa_base' => $money($valorTarifaBase),
                'valor_opcionais' => $money($valorOpcionais),
                'taxa_coleta' => $money($taxaColeta),
                'taxa_entrega' => $money($taxaEntrega),
                'subtotal' => $money($subtotal),
                'percentual_desconto' => number_format($percentualDesconto, 2, ',', '.').'%',
                'valor_desconto' => $money($valorDesconto),
                'iva_total' => $money(0),
                'valor_total' => $money($valorTotal),
                'total_pago' => $money($totalPago),
                'saldo_pendente' => $money($saldoPendente),
                'status_financeiro' => $saldoPendente <= 0 ? 'Pago' : ($totalPago > 0 ? 'Parcialmente pago' : 'Em aberto'),
            ],
            'funcionario' => ['nome' => Auth::user()->nome ?? Auth::user()->name ?? 'Sistema'],
            'opcionais' => $opcionais,
            'assinatura' => $reserva->contratoCheckin->assinatura,
            'financeiro_detalhado' => [
                'grupo' => $motoristaApp ? $safe($motoristaApp->planoMotoristaApp?->nome) : $safe($reserva->grupoFrota?->nome),
                'tarifa_diaria' => $money($tarifaBase),
                'dias_label' => $motoristaApp ? ($dias.' '.($dias === 1 ? 'dia' : 'dias').' x tarifa') : ($dias.' '.($dias === 1 ? 'dia' : 'dias').' x tarifa'),
                'valor_tarifa_base' => $money($valorTarifaBase),
                'opcionais' => $money($valorOpcionais),
                'taxa_coleta' => $money($taxaColeta),
                'taxa_entrega' => $money($taxaEntrega),
                'subtotal' => $money($subtotal),
                'desconto_label' => number_format($percentualDesconto, 2, ',', '.').'%',
                'desconto_valor' => $money($valorDesconto),
                'total' => $money($valorTotal),
            ],
            'pagamentos' => $motoristaApp
                ? $motoristaApp->pagamentos->sortByDesc('id')->map(fn ($t) => [
                    'data' => $dateTime($t->created_at),
                    'valor' => $money($t->valor),
                    'metodo' => $t->metodo_pagamento ?? ($t->forma_pagamento ?? '-'),
                    'referencia' => $t->referencia_pagamento ?? ($t->payment_intent_id ?? ($t->transacao_id ?? '-')),
                    'status' => $t->status ?? '-',
                    'confirmado_em' => $dateTime($t->created_at),
                    'pago_por' => '-',
                ])->values()->toArray()
                : $reserva->transacoes->sortByDesc('id')->map(fn ($t) => [
                    'data' => $dateTime($t->created_at),
                    'valor' => $money($t->valor),
                    'metodo' => $t->metodo_pagamento ?? '-',
                    'referencia' => $t->referencia_pagamento ?? ($t->payment_intent_id ?? '-'),
                    'status' => $t->status ?? '-',
                    'confirmado_em' => $dateTime($t->confirmado_em),
                    'pago_por' => $t->pagoPor?->nome ?? $t->user?->nome ?? '-',
                ])->values()->toArray(),
            'danos_detalhados' => $danos,
            'condicoes_gerais' => (static function () use ($reserva, $motoristaApp, $tipoContrato) {
                $tipoContrato = $tipoContrato ?? ($reserva->contratoCheckout ? 'checkout' : 'checkin');
                $ehMotoristaApp = (bool) $motoristaApp;

                if ($tipoContrato === 'checkout') {
                    $tipoTermo = $ehMotoristaApp ? 'checkout-motorista-app' : 'checkout-reserva';
                    $fallbackTipo = 'checkout-reserva';
                } else {
                    $tipoTermo = $ehMotoristaApp ? 'checkin-motorista-app' : 'checkin-reserva';
                    $fallbackTipo = 'checkin-reserva';
                }

                $condicoes = TermoConsentimento::where('empresa_id', $reserva->empresa_id)
                    ->where('tipo', $tipoTermo)
                    ->where('ativo', 'S')
                    ->orderBy('ordem')
                    ->get(['ordem', 'titulo', 'texto'])
                    ->toArray();

                if (empty($condicoes)) {
                    $condicoes = TermoConsentimento::where('empresa_id', $reserva->empresa_id)
                        ->where('tipo', $fallbackTipo)
                        ->where('ativo', 'S')
                        ->orderBy('ordem')
                        ->get(['ordem', 'titulo', 'texto'])
                        ->toArray();
                }

                return $condicoes;
            })(),
        ];
    }
}

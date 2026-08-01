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
        $reserva->loadMissing([
            'contratoCheckin',
            'contratoCheckin.veiculo.marca',
            'contratoCheckin.veiculo.modelo',
            'contratoCheckin.veiculo.grupoFrota',
            'contratoCheckin.marca',
            'contratoCheckin.modelo',
            'empresa',
            'usuario',
            'grupoFrota',
            'modelos',
            'statusReserva',
            'estacaoRetirada',
            'estacaoDevolucao',
            'opcionais',
            'transacoes',
            'calcao.configuracao',
            'veiculoDanos.dano.categoria',
            'veiculoDanos.imagem',
            'veiculoDanos.usuarioCorrecao',
        ]);

        $contratoCheckin = $reserva->contratoCheckin;

        if (! $contratoCheckin) {
            throw new \RuntimeException(
                'O contrato de check-in desta reserva não foi encontrado.'
            );
        }

        /*
     * Contrato assinado: nunca gera novamente.
     * Retorna obrigatoriamente o PDF salvo.
     */
        if ($contratoCheckin->assinado === 'S') {
            if (blank($contratoCheckin->pdf_path)) {
                throw new \RuntimeException(
                    'O contrato está assinado, mas não possui um PDF salvo.'
                );
            }

            if (! Storage::disk('public')->exists($contratoCheckin->pdf_path)) {
                throw new \RuntimeException(
                    'O contrato está assinado, mas o arquivo PDF salvo não foi encontrado.'
                );
            }

            return Storage::disk('public')->get($contratoCheckin->pdf_path);
        }

        /*
     * Contrato ainda não assinado: gera apenas para visualização.
     */
        $data = $this->montarDados($reserva, 'checkin');

        return $this->pdfService->gerarConteudo(
            view: 'pdf.contratos.checkin.comum',
            data: $data,
        );
    }

    public function montarDados(Reserva $reserva, ?string $tipoContrato = null): array
    {
        $reserva->loadMissing(['calcao', 'calcao.configuracao']);

        $calcaoRecord = $reserva->calcao;
        $valorCalculado = 0.0;
        $metodoCalcao = 'Padrão';
        $statusCalcao = 'Pendente';

        try {
            $calcaoService = app(\App\Services\CalcaoService::class);
            $calculo = $calcaoService->calcularValor($reserva);
            $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);
            if (! empty($calculo['configuracao'])) {
                $metodoCalcao = $calculo['configuracao']->nome ?? 'Padrão';
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Problema ao calcular o calção: ' . $e->getMessage());
        }

        $moedaSimbolo = match (strtoupper($reserva->empresa?->moeda_pagamento ?? 'BRL')) {
            'USD' => '$',
            'EUR' => '€',
            default => 'R$',
        };

        $money = fn($v) => $moedaSimbolo . ' ' . number_format((float) ($v ?? 0), 2, ',', '.');
        $dateTime = fn($v) => $v ? Carbon::parse($v)->format('d/m/Y H:i') : '-';
        $dateOnly = fn($v) => $v ? Carbon::parse($v)->format('d/m/Y') : '-';
        $safe = fn($v, $d = '-') => filled($v) ? $v : $d;

        $usuario = $reserva->usuario;
        $dias = (int) ($reserva->quantidade_dias ?? 0);
        $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
        $valorTarifaBase = (float) ($reserva->valor_total_tarifa_base ?? 0) ?: $tarifaBase * max(1, $dias);
        $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);

        if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
            $valorOpcionais = (float) $reserva->opcionais->sum(fn($o) => (float) ($o->pivot->preco_total ?? 0));
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

        $opcionais = $reserva->opcionais->map(function ($opcional) use ($dias, $money) {
            $quantidade = (int) ($opcional->pivot->quantidade ?? 1);
            $tipoCobranca = $opcional->tipo_cobranca;

            $precoUnitario = $tipoCobranca === 'por_dia'
                ? (float) ($opcional->preco_por_dia ?? 0)
                : (float) ($opcional->preco_integral ?? 0);

            $periodo = $tipoCobranca === 'por_dia' ? max(1, $dias) : 1;
            $valorTotal = (float) ($opcional->pivot->preco_total ?? 0);

            if ($valorTotal <= 0) {
                $valorTotal = $precoUnitario * $quantidade * $periodo;
            }

            return [
                'nome' => $opcional->nome,
                'tipo_cobranca' => $tipoCobranca,
                'tipo_cobranca_formatado' => match ($tipoCobranca) {
                    'por_dia' => 'Por dia',
                    'valor_integral' => 'Valor integral',
                    default => '-',
                },
                'quantidade' => $quantidade,
                'periodo' => $tipoCobranca === 'por_dia' ? "{$periodo} dias" : '-',
                'preco_unitario' => $money($precoUnitario),
                'valor_total' => $money($valorTotal),
            ];
        })->toArray();

        $logoData = null;
        $logoPath = is_string($reserva->empresa?->logo_path ?? null) ? trim($reserva->empresa->logo_path) : '';
        if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            $mime = mime_content_type(Storage::disk('public')->path($logoPath));
            $logoData = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($logoPath));
        }

        $danos = $reserva->veiculoDanos->sortByDesc('id')->map(fn($d) => [
            'id' => $d->id,
            'localizacao' => strtoupper((string) ($d->local_dano ?? '-')),
            'categoria' => $d->dano?->categoria?->nome ?? '-',
            'localizacao_problema' => $safe($d->localizacao_problema),
            'tipo_dano' => $safe($d->tipo_dano),
            'observacoes' => $safe($d->observacoes),
            'quilometragem' => $d->quilometragem_veiculo ? number_format((float) $d->quilometragem_veiculo, 0, ',', '.') . ' km' : '-',
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

                return 'data:' . $m . ';base64,' . base64_encode(Storage::disk('public')->get($p));
            })->filter()->values()->toArray(),
        ])->values()->toArray();

        return [
            'documento' => [
                'codigo_interno' => 'ARCS-' . date('y'),
                'atcud' => 'JJNXXNNC-' . date('y'),
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
                'numero' => '#' . str_pad($reserva->id, 5, '0', STR_PAD_LEFT),
                'titulo' => 'CONTRATO DE ALUGUEL',
                'subtitulo' => 'CONDIÇÕES PARTICULARES DO CONTRATO DE ALUGUEL',
                'periodicidade_label' => 'Ciclo',
                'periodicidade' => 'Ciclo',
                'assinatura' => $reserva->contratoCheckin->assinatura ?? null,
                'data_assinatura' => $reserva->contratoCheckin
                    ? ($reserva->contratoCheckin->data_assinatura ? Carbon::parse($reserva->contratoCheckin->data_assinatura)->format('d/m/Y H:i') : null)
                    : null,
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
                'id' => $reserva->id,
                'status' => $safe($reserva->statusReserva?->nome),
                'origem' => $safe($reserva->label_origem_cadastro),
                'criada_em' => $dateTime($reserva->created_at),
                'pagamento_efetuado' => (($reserva->pagamento_efetuado ?? 'N') === 'S'),
                'grupo_reservado' => $safe($reserva->grupoFrota?->nome),
                'modelo_reservado' => $safe($reserva->modelos?->nome),
                'observacoes' => $safe($reserva->observacoes),
                'estacao_retirada' => $safe($reserva->estacaoRetirada?->nome),
                'data_inicio' => $dateTime($reserva->data_inicio),
                'data_fim' => $dateTime($reserva->data_fim),
                'plano_nome' => $safe($reserva->grupoFrota?->nome),
                'quantidade_dias' => $dias,
                'alugado_em' => [
                    'local' => $safe($reserva->estacaoRetirada?->nome),
                    'data' => $dateOnly($reserva->data_inicio),
                    'hora' => $reserva->data_inicio ? Carbon::parse($reserva->data_inicio)->format('H:i') : '-',
                ],
                'devolver_em' => [
                    'local' => $safe($reserva->estacaoDevolucao?->nome),
                    'data' => $dateOnly($reserva->data_fim),
                    'hora' => $reserva->data_fim ? Carbon::parse($reserva->data_fim)->format('H:i') : '-',
                ],
            ],
            'valores' => [
                'valor_periodo' => $valorTarifaBase,
                'valor_opcionais' => $valorOpcionais,
                'valor_total' => $valorTotal,
                'caucao' => (float) (
                    $reserva->calcao?->valor_capturado
                    ?? $reserva->calcao?->valor_autorizado
                    ?? $reserva->calcao?->valor_calculado
                    ?? $valorCalculado
                ),
            ],
            'caucao' => [
                'valor_capturado' => $reserva->calcao?->valor_capturado ? $money($reserva->calcao->valor_capturado) : null,
                'valor_autorizado' => $reserva->calcao?->valor_autorizado ? $money($reserva->calcao->valor_autorizado) : null,
                'valor_calculado' => $money($reserva->calcao?->valor_calculado ?? $valorCalculado),
                'status' => $reserva->calcao?->label_status ?? $statusCalcao,
                'metodo' => $reserva->calcao?->configuracao?->nome ?? $metodoCalcao,
            ],
            'faturacao' => [
                'dias_unidades' => $dias,
                'tarifa_diaria' => $money($tarifaBase),
                'valor_tarifa_base' => $money($valorTarifaBase),
                'valor_opcionais' => $money($valorOpcionais),
                'taxa_coleta' => $money($taxaColeta),
                'taxa_entrega' => $money($taxaEntrega),
                'subtotal' => $money($subtotal),
                'percentual_desconto' => number_format($percentualDesconto, 2, ',', '.') . '%',
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
                'grupo' => $safe($reserva->grupoFrota?->nome),
                'tarifa_diaria' => $money($tarifaBase),
                'dias_label' => $dias . ' ' . ($dias === 1 ? 'dia' : 'dias') . ' x tarifa',
                'valor_tarifa_base' => $money($valorTarifaBase),
                'opcionais' => $money($valorOpcionais),
                'taxa_coleta' => $money($taxaColeta),
                'taxa_entrega' => $money($taxaEntrega),
                'subtotal' => $money($subtotal),
                'desconto_label' => number_format($percentualDesconto, 2, ',', '.') . '%',
                'desconto_valor' => $money($valorDesconto),
                'total' => $money($valorTotal),
            ],
            'pagamentos' => $reserva->transacoes->sortByDesc('id')->map(fn($t) => [
                'data' => $dateTime($t->created_at),
                'valor' => $money($t->valor),
                'metodo' => $t->metodo_pagamento ?? '-',
                'referencia' => $t->referencia_pagamento ?? ($t->payment_intent_id ?? '-'),
                'status' => $t->status ?? '-',
                'confirmado_em' => $dateTime($t->confirmado_em),
                'pago_por' => $t->pagoPor?->nome ?? $t->user?->nome ?? '-',
            ])->values()->toArray(),
            'danos_detalhados' => $danos,
            'condicoes_gerais' => (static function () use ($reserva, $tipoContrato) {
                $tipoContrato = $tipoContrato ?? ($reserva->contratoCheckout ? 'checkout' : 'checkin');
                $tipoTermo = $tipoContrato === 'checkout' ? 'checkout-reserva' : 'checkin-reserva';

                return TermoConsentimento::where('empresa_id', $reserva->empresa_id)
                    ->where('tipo', $tipoTermo)
                    ->where('ativo', 'S')
                    ->orderBy('ordem')
                    ->get(['ordem', 'titulo', 'texto'])
                    ->toArray();
            })(),
        ];
    }
}

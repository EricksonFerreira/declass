<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Actions\Reserva\Renovacao\RenovacaoStrategyInterface;
use App\Models\Cupom;
use App\Models\PlanoMotoristaApp;
use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppPagamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Empresa;

class RenovarMotoristaAppAction implements RenovacaoStrategyInterface
{
    /**
     * Executa a renovação de uma reserva de motorista de app.
     * Suporta:
     *   - Manter plano atual ou trocar para outro plano
     *   - Aplicação de cupom de desconto
     *   - Seleção de opcionais
     *
     * @param  Request               $request
     * @param  ReservaMotoristaApp   $reserva
     * @return ReservaMotoristaAppPagamento
     */
    public function handle(Request $request, mixed $reserva): ReservaMotoristaAppPagamento
    {
        // Verify company is active
        $empresa = $reserva->empresa ?? null;
        if (!$empresa || $empresa->ativo !== 'S') {
            throw new \RuntimeException('Empresa não está ativa. Renovação não permitida.');
        }

        $acaoRenovacao = (string) ($request->input('acao_renovacao', 'mantem'));
        $periodos      = max(1, (int) $request->input('periodos', 1));
        $observacoes   = (string) $request->input('observacoes', '');
        $cupomCodigo   = trim((string) $request->input('cupom_codigo', ''));
        $opcionaisIds  = (array) $request->input('opcionais', []);

        // ── 1. Definir o plano a usar ──────────────────────────────────────
        $planoAtual = $reserva->planoMotoristaApp;
        if (!$planoAtual) {
            throw new \RuntimeException('Plano não encontrado para esta reserva.');
        }

        $plano = $planoAtual;
        $trocouPlano = false;

        if ($acaoRenovacao === 'trocar') {
            $novoPlanoId = (int) $request->input('plano_id');
            if ($novoPlanoId && $novoPlanoId !== (int) $planoAtual->id) {
                $novoPlano = PlanoMotoristaApp::find($novoPlanoId);
                if ($novoPlano) {
                    $plano = $novoPlano;
                    $trocouPlano = true;
                }
            }
        }

        // ── 2. Calcular valor unitário ─────────────────────────────────────
        if ($trocouPlano) {
            $valorUnitario = (float) $plano->valor_periodo;
        } else {
            // Usar valor contratado da reserva (pode diferir do plano atual)
            $valorUnitario = (float) (
                $reserva->valor_periodo_contrato
                ?: $reserva->valor_plano
                ?: $plano->valor_periodo
                ?: 0
            );
        }

        if ($valorUnitario <= 0) {
            throw new \RuntimeException('Não foi possível identificar o valor do plano para renovação.');
        }

        // ── 3. Calcular opcionais ──────────────────────────────────────────
        $valorOpcionais = 0.0;
        $opcionaisSnapshot = [];

        if (!empty($opcionaisIds)) {
            $opcionaisPlano = $plano->opcionais()->whereIn('opcionais.id', $opcionaisIds)->get();
            foreach ($opcionaisPlano as $opc) {
                $valorOpcionais += (float) ($opc->pivot->valor ?? $opc->valor ?? 0);
                $opcionaisSnapshot[] = [
                    'id'    => $opc->id,
                    'nome'  => $opc->nome,
                    'valor' => (float) ($opc->pivot->valor ?? $opc->valor ?? 0),
                ];
            }
        }

        // ── 4. Aplicar cupom de desconto ───────────────────────────────────
        $descontoCupom    = 0.0;
        $cupomSnapshot    = null;

        if ($cupomCodigo !== '') {
            $cupom = Cupom::where('codigo', $cupomCodigo)
                ->where('ativo', true)
                ->first();

            if (!$cupom) {
                throw new \RuntimeException("Cupom '{$cupomCodigo}' não encontrado ou inativo.");
            }

            if ($cupom->validade && Carbon::parse($cupom->validade)->isPast()) {
                throw new \RuntimeException("O cupom '{$cupomCodigo}' está expirado.");
            }

            $baseDesconto = ($valorUnitario * $periodos) + ($valorOpcionais * $periodos);

            if ((string) $cupom->tipo === 'percentual') {
                $descontoCupom = round($baseDesconto * ((float) $cupom->valor / 100), 2);
            } else {
                $descontoCupom = min((float) $cupom->valor, $baseDesconto);
            }

            $cupomSnapshot = [
                'id'          => $cupom->id,
                'codigo'      => $cupom->codigo,
                'tipo'        => $cupom->tipo,
                'valor'       => $cupom->valor,
                'desconto'    => $descontoCupom,
            ];
        }

        // ── 5. Calcular vigência ───────────────────────────────────────────
        $periodoContrato = (string) ($reserva->periodo_contrato ?: $plano->periodo ?: 'semana');

        $inicioBase = $reserva->data_fim
            ? Carbon::parse($reserva->data_fim)->addDay()
            : Carbon::now();

        if (empty($reserva->data_fim)) {
            throw new \RuntimeException('Não foi possível identificar o fim do ciclo atual.');
        }

        $novaVigenciaFim = $this->aplicarPeriodo($inicioBase->copy(), $periodoContrato, $periodos);
        $quantidadeDias  = max(1, $inicioBase->diffInDays($novaVigenciaFim));

        // ── 6. Calcular total ──────────────────────────────────────────────
        $valorBruto = round(($valorUnitario * $periodos) + ($valorOpcionais * $periodos), 2);
        $valorTotal = max(0, round($valorBruto - $descontoCupom, 2));
        $periodoUnidade = $this->rotuloPeriodo($periodoContrato);

        // ── 7. Criar pagamento ─────────────────────────────────────────────
        $metadados = [
            'plano_id'                  => (int) $plano->id,
            'plano_nome'                => $plano->nome,
            'acao_renovacao'            => $acaoRenovacao,
            'trocou_plano'              => $trocouPlano,
            'plano_anterior_id'         => $trocouPlano ? (int) $planoAtual->id : null,
            'valor_plano_unitario'      => $valorUnitario,
            'valor_opcionais_por_ciclo' => $valorOpcionais,
            'quantidade_dias_prevista'  => $quantidadeDias,
            'opcionais'                 => $opcionaisSnapshot,
            'cupom'                     => $cupomSnapshot,
            'desconto_cupom'            => $descontoCupom,
        ];

        $pagamento = ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => (int) $reserva->id,
            'empresa_id'               => $reserva->empresa_id ?? Auth::user()?->empresa_id ?? null,
            'user_id'                  => (int) (Auth::id() ?? $reserva->user_id),
            'periodos'                 => $periodos,
            'periodo_unidade'          => $periodoUnidade,
            'valor_unitario'           => $valorUnitario,
            'valor_total'              => $valorTotal,
            'data_inicio_prevista'     => $inicioBase->copy(),
            'data_fim_prevista'        => $novaVigenciaFim,
            'status'                   => 'pendente',
            'observacoes'              => $observacoes ?: null,
            'metadados'                => $metadados,
        ]);

        // ── 8. Se trocou plano, atualizar o plano na reserva ──────────────
        if ($trocouPlano) {
            $reserva->plano_motorista_app_id  = $plano->id;
            $reserva->valor_plano             = $valorUnitario;
            $reserva->valor_periodo_contrato  = $valorUnitario;
            $reserva->periodo_contrato        = $periodoContrato;
            $reserva->save();
        }

        return $pagamento;
    }

    // ── Helpers internos ───────────────────────────────────────────────────

    private function aplicarPeriodo(Carbon $inicio, string $periodo, int $periodos): Carbon
    {
        $periodos = max(1, $periodos);

        return match ($periodo) {
            'quinze'    => $inicio->copy()->addDays(15 * $periodos),
            'mes'       => $inicio->copy()->addMonthsNoOverflow($periodos),
            'trimestre' => $inicio->copy()->addMonthsNoOverflow(3 * $periodos),
            'semestre'  => $inicio->copy()->addMonthsNoOverflow(6 * $periodos),
            'ano'       => $inicio->copy()->addYearsNoOverflow($periodos),
            default     => $inicio->copy()->addWeeks($periodos),
        };
    }

    private function rotuloPeriodo(string $periodo): string
    {
        return match ($periodo) {
            'quinze'    => 'Quinze dias',
            'mes'       => 'Mês',
            'trimestre' => 'Trimestre',
            'semestre'  => 'Semestre',
            'ano'       => 'Ano',
            default     => 'Semana',
        };
    }

    // ── Manter compatibilidade com chamadas legadas via callables ─────────

    public function handleLegado(
        ReservaMotoristaApp $reserva,
        callable $valorUnitarioCalc,
        callable $aplicarPeriodoCalc,
        callable $rotuloPeriodoCalc,
    ): ReservaMotoristaAppPagamento {
        $periodos    = max(1, (int) request('periodos', 1));
        $observacoes = (string) request('observacoes', '');

        $plano = $reserva->planoMotoristaApp;
        if (!$plano) {
            throw new \RuntimeException('Plano nao encontrado para esta reserva.');
        }

        $valorUnitario = $valorUnitarioCalc($reserva);
        if ($valorUnitario <= 0) {
            throw new \RuntimeException('Nao foi possivel identificar o valor do plano para renovacao.');
        }

        $periodoContrato = (string) ($reserva->periodo_contrato ?: $plano->periodo ?: 'semana');
        $inicioBase      = $reserva->data_fim ? Carbon::parse($reserva->data_fim) : Carbon::now();

        if (empty($reserva->data_fim)) {
            throw new \RuntimeException('Nao foi possivel identificar o fim do ciclo atual.');
        }

        $periodoUnidade  = $rotuloPeriodoCalc($periodoContrato);
        $novaVigenciaFim = $aplicarPeriodoCalc($inicioBase->copy(), $periodoContrato, $periodos);
        $quantidadeDias  = max(1, $inicioBase->diffInDays($novaVigenciaFim));
        $valorTotal      = round($valorUnitario * $periodos, 2);

        $pagamento = ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => (int) $reserva->id,
            'empresa_id'               => $reserva->empresa_id ?? Auth::user()?->empresa_id ?? null,
            'user_id'                  => (int) (Auth::id() ?? $reserva->user_id),
            'periodos'                 => $periodos,
            'periodo_unidade'          => $periodoUnidade,
            'valor_unitario'           => $valorUnitario,
            'valor_total'              => $valorTotal,
            'data_inicio_prevista'     => $inicioBase->copy(),
            'data_fim_prevista'        => $novaVigenciaFim,
            'status'                   => 'pendente',
            'observacoes'              => $observacoes ?: null,
            'metadados'                => [
                'plano_id'               => (int) $plano->id,
                'valor_plano_atual'      => $valorUnitario,
                'quantidade_dias_prevista' => $quantidadeDias,
            ],
        ]);

        return $pagamento;
    }
}

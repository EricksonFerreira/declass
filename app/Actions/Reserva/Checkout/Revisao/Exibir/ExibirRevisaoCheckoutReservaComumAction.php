<?php

namespace App\Actions\Reserva\Checkout\Revisao\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Models\AvariaCobranca;
use App\Models\CategoriaDano;
use App\Models\VeiculoDanos;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;

class ExibirRevisaoCheckoutReservaComumAction
{
    public function __construct(
        private BreadcrumbService $breadcrumbService,
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;
        $registro->loadMissing([
            'usuario',
            'empresa',
            'veiculo.marca',
            'veiculo.modelo',
            'contratoCheckin',
            'contratoCheckout',
            'calcao',
            'avariaCobrancas',
            'veiculoDanos.dano.categoria',
            'veiculoDanos.imagem',
            'veiculoDanos.usuarioCorrecao',
            'veiculoDanos.reservaCodigo',
            'transacoes',
        ]);

        $checkout = $registro->contratoCheckout;

        if (! $checkout) {
            return redirect()
                ->route('reserva.show', $reserva->codigo)
                ->with('warning', 'Nenhum checkout encontrado para revisão.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$reserva->codigo, route('reserva.show', $reserva->codigo)),
            new BreadCrumbItem('Revisão do Check-out'),
        ]);

        $categoriasDano = CategoriaDano::query()
            ->where('ativo', 'S')
            ->with(['danos'])
            ->orderBy('nome')
            ->get();

        $danosVeiculo = $this->montarDanosRevisao($registro);
        $danosPendentes = $danosVeiculo->filter(fn ($dano) => ! $this->isDanoSinalizado($dano))->count();
        $danosCobrar = $danosVeiculo->filter(fn ($dano) => (bool) ($dano['deve_cobrar'] ?? false))->count();
        $danosNaoCobrar = $danosVeiculo->filter(fn ($dano) => in_array(($dano['status_revisao'] ?? ''), [
            VeiculoDanos::REVISAO_NAO_COBRAR,
            VeiculoDanos::REVISAO_JA_EXISTENTE,
            VeiculoDanos::REVISAO_DESGASTE_NORMAL,
        ], true))->count();
        $resumoFinanceiro = $this->montarResumoFinanceiro($registro, $danosVeiculo);

        return view('reserva.revisao-checkout', [
            'reserva' => $registro,
            'checkout' => $checkout,
            'danosVeiculo' => $danosVeiculo,
            'cobrancasAvaria' => $registro->avariaCobrancas ?? collect(),
            'categoriasDano' => $categoriasDano,
            'categoriaDanos' => $categoriasDano,
            'cobrancaTotal' => $resumoFinanceiro['total_cobrancas'],
            'resumoFinanceiro' => $resumoFinanceiro,
            'danosPendentes' => $danosPendentes,
            'danosCobrar' => $danosCobrar,
            'danosNaoCobrar' => $danosNaoCobrar,
            'breadcrumb' => $breadcrumb,
            'titulo' => 'Revisão do Check-out - Reserva Nº '.$registro->id,
        ]);
    }

    private function montarDanosRevisao($registro)
    {
        $cobrancasPorDano = $this->indexarCobrancasPorDano($registro->avariaCobrancas ?? collect());
        $reservaCodigoAtual = (int) ($registro->codigoReserva?->id ?? 0);
        $reservaIdAtual = (int) ($registro->id ?? 0);

        return ($registro->veiculoDanos ?? collect())
            ->map(function (VeiculoDanos $dano) use ($cobrancasPorDano, $reservaCodigoAtual, $reservaIdAtual) {
                $cobranca = $cobrancasPorDano->get($dano->id);
                $valorSugerido = $this->obterValorSugerido($dano);
                $cobrancaStatus = (string) ($cobranca?->status ?? '');
                $cobrancaPendente = $this->isCobrancaPendente($cobranca);
                $deveCobrar = $this->deveCobrarDano($dano, $cobranca, $valorSugerido);
                $origemLabel = $this->obterOrigemLabel($dano, $reservaCodigoAtual, $reservaIdAtual);
                $cobrancaInfo = $this->obterInfoCobranca($cobranca);

                return [
                    'id' => $dano->id,
                    'model' => $dano,
                    'local' => strtoupper((string) ($dano->local_dano ?? '-')),
                    'descricao' => $this->obterDescricaoDano($dano),
                    'observacoes' => trim((string) ($dano->observacoes ?? '')),
                    'status' => $dano->status ?? VeiculoDanos::STATUS_PENDENTE,
                    'status_label' => $this->obterStatusLabel($dano, $cobrancaStatus, $cobrancaPendente),
                    'deve_cobrar' => $deveCobrar,
                    'motivo_cobranca' => $this->obterMotivoCobranca($dano, $cobranca, $valorSugerido),
                    'valor_sugerido' => $valorSugerido,
                    'valor_aprovado' => (float) ($cobranca?->valor ?? 0),
                    'valor_exibido' => (float) ($cobranca?->valor ?? $valorSugerido),
                    'valor_cobranca' => (float) ($cobranca?->valor ?? 0),
                    'valor_calcao' => (float) ($cobranca?->valor_calcao ?? 0),
                    'valor_pendente' => (float) ($cobranca?->valor_pendente ?? 0),
                    'usar_calcao' => (bool) ($cobrancaInfo['usar_calcao'] ?? false),
                    'cobranca_descricao' => (string) ($cobranca?->descricao ?? ''),
                    'cobranca_metodo' => (string) ($cobranca?->metodo ?? ''),
                    'cobranca_info' => $cobrancaInfo,
                    'ja_cobrado' => $this->isCobrancaEfetivada($cobranca),
                    'cobranca_pendente' => $cobrancaPendente,
                    'cobranca_id' => $cobranca?->id,
                    'origem_label' => $origemLabel,
                    'imagens' => $this->mapearImagens($dano),
                ];
            })
            ->values();
    }

    private function obterOrigemLabel(VeiculoDanos $dano, int $reservaCodigoAtual, int $reservaIdAtual): array
    {
        if (! empty($dano->contrato_id)) {
            return ['label' => 'Check-in', 'class' => 'mini-chip--info'];
        }

        if ($reservaCodigoAtual > 0 && (int) ($dano->reserva_codigo_id ?? 0) === $reservaCodigoAtual) {
            return ['label' => 'Check-out', 'class' => 'mini-chip--warn'];
        }

        if (($dano->reserva_id ?? null) && (int) $dano->reserva_id !== $reservaIdAtual) {
            return ['label' => 'Outra reserva', 'class' => 'mini-chip--ok'];
        }

        return ['label' => 'Outra reserva', 'class' => 'mini-chip--ok'];
    }

    private function indexarCobrancasPorDano($cobrancas)
    {
        return collect($cobrancas)->reduce(function ($carry, AvariaCobranca $cobranca) {
            foreach ((array) ($cobranca->dano_veiculo_ids ?? []) as $danoId) {
                $carry->put((int) $danoId, $cobranca);
            }

            return $carry;
        }, collect());
    }

    private function obterInfoCobranca(?AvariaCobranca $cobranca): array
    {
        if (! $cobranca) {
            return [];
        }

        $checkout = (array) ($cobranca->gateway_response['checkout_revisao'] ?? []);
        $stripe = (array) ($cobranca->gateway_response['stripe'] ?? []);
        $calcao = (array) ($cobranca->gateway_response['calcao'] ?? []);

        return [
            'descricao' => trim((string) ($cobranca->descricao ?? '')),
            'valor' => (float) ($cobranca->valor ?? 0),
            'usar_calcao' => (bool) ($checkout['usar_calcao'] ?? (($cobranca->valor_calcao ?? 0) > 0 || ($calcao['valor'] ?? 0) > 0)),
            'valor_calcao' => (float) ($checkout['valor_calcao'] ?? ($cobranca->valor_calcao ?? 0)),
            'valor_pendente' => (float) ($checkout['valor_pendente'] ?? ($cobranca->valor_pendente ?? 0)),
            'metodo' => (string) ($cobranca->metodo ?? ''),
            'status' => (string) ($cobranca->status ?? ''),
            'gateway' => [
                'stripe' => $stripe,
                'calcao' => $calcao,
            ],
        ];
    }

    private function deveCobrarDano(VeiculoDanos $dano, ?AvariaCobranca $cobranca, float $valorSugerido): bool
    {
        if (($dano->status ?? null) === VeiculoDanos::STATUS_DESCARTADA) {
            return false;
        }

        if ($this->isCobrancaPendente($cobranca)) {
            return true;
        }

        if ($this->isCobrancaEfetivada($cobranca)) {
            return false;
        }

        return false;

        // return $valorSugerido > 0;
    }

    private function obterValorSugerido(VeiculoDanos $dano): float
    {
        return (float) ($dano->custo_estimado ?? $dano->dano?->custo_estimado ?? 0);
    }

    private function obterDescricaoDano(VeiculoDanos $dano): string
    {
        return trim((string) ($dano->localizacao_problema ?? $dano->dano?->nome ?? 'Dano'));
    }

    private function obterStatusLabel(VeiculoDanos $dano, string $cobrancaStatus = '', bool $cobrancaPendente = false): string
    {
        if ($cobrancaPendente) {
            return 'Cobrança pendente';
        }

        if ($this->isCobrancaEfetivadaStatus($cobrancaStatus)) {
            return 'Cobrança confirmada';
        }

        return match ($dano->status ?? VeiculoDanos::STATUS_PENDENTE) {
            VeiculoDanos::STATUS_EM_CORRECAO => 'Em correção',
            VeiculoDanos::STATUS_CORRIGIDA => 'Corrigido',
            VeiculoDanos::STATUS_PARCIALMENTE_CORRIGIDA => 'Parcialmente corrigido',
            VeiculoDanos::STATUS_DESCARTADA => 'Descartado',
            default => 'Pendente',
        };
    }

    private function obterMotivoCobranca(VeiculoDanos $dano, ?AvariaCobranca $cobranca, float $valorSugerido): string
    {
        if (($dano->status ?? null) === VeiculoDanos::STATUS_DESCARTADA) {
            return 'Este dano foi descartado e não entra na cobrança.';
        }

        if ($this->isCobrancaPendente($cobranca)) {
            return 'Cobrança salva como pendente e editável até a finalização.';
        }

        if ($this->isCobrancaEfetivada($cobranca)) {
            return 'Este dano já possui cobrança finalizada.';
        }

        if ($valorSugerido <= 0) {
            return 'Sem valor estimado para cobrança.';
        }

        return 'Dano com valor estimado e sem cobrança vinculada.';
    }

    private function calcularCobrancaTotal($danosRevisados): float
    {
        return (float) collect($danosRevisados)
            ->filter(fn ($dano) => (bool) ($dano['deve_cobrar'] ?? false))
            ->sum(fn ($dano) => (float) ($dano['valor_sugerido'] ?? 0));
    }

    private function montarResumoFinanceiro($registro, $danosVeiculo): array
    {
        $cobrancas = collect($registro->avariaCobrancas ?? []);
        $valorCaucao = (float) (
            $registro->calcao?->valor_capturado
            ?? $registro->calcao?->valor_autorizado
            ?? $registro->calcao?->valor_calculado
            ?? $registro->calcao?->valor
            ?? 0
        );

        $danosComCobranca = $danosVeiculo->filter(fn ($dano) => (bool) ($dano['deve_cobrar'] ?? false));
        $totalCobrancas = (float) $danosComCobranca->sum(fn ($dano) => (float) ($dano['valor_exibido'] ?? $dano['valor_sugerido'] ?? 0));
        // dd($danosComCobranca);
        $valorUsadoSolicitado = (float) $danosComCobranca
            ->filter(fn ($dano) => (bool) ($dano['usar_calcao'] ?? false))
            ->sum(fn ($dano) => (float) ($dano['valor_sugerido'] ?? 0));

        $valorUsadoCaucao = min($valorUsadoSolicitado, $totalCobrancas, $valorCaucao);
        $valorAindaCobrar = max(0, $totalCobrancas - $valorUsadoCaucao);
        $valorDevolver = max(0, $valorCaucao - $totalCobrancas);

        $totalComCalcao = $valorUsadoCaucao;
        $totalSemCalcao = $valorAindaCobrar;

        return [
            'total_avarias' => (float) $danosVeiculo->sum(fn ($dano) => (float) ($dano['valor_exibido'] ?? $dano['valor_sugerido'] ?? 0)),
            'total_combustivel' => 0,
            'total_limpeza' => 0,
            'total_diaria_extra' => 0,
            'total_outros' => 0,
            'total_cobrancas' => $totalCobrancas,
            'resultado_revisao' => $totalCobrancas,
            'valor_caucao' => $valorCaucao,
            'valor_usado_caucao' => $valorUsadoCaucao,
            'valor_ainda_cobrar' => $valorAindaCobrar,
            'valor_devolver' => $valorDevolver,
            'total_com_calcao' => $totalComCalcao,
            'total_sem_calcao' => $totalSemCalcao,
            'total_previsto' => $totalCobrancas,
            'total_pendente' => $valorAindaCobrar,
            'total_cobrado' => $totalUsadoCaucao = $valorUsadoCaucao,
            'valor_calcao_utilizado' => $valorUsadoCaucao,
            'valor_calcao_restante' => $valorDevolver,
            'valor_calcao_autorizado' => $valorCaucao,
            'qtd_com_calcao' => $danosComCobranca->filter(fn ($dano) => (bool) ($dano['usar_calcao'] ?? false))->count(),
            'qtd_sem_calcao' => $danosComCobranca->filter(fn ($dano) => ! (bool) ($dano['usar_calcao'] ?? false))->count(),
            'cobrancas_pendentes' => 0,
            'cobrancas_pagas' => 0,
            'cobrancas_canceladas' => 0,
            'danos_total' => $danosVeiculo->count(),
            'danos_cobrar' => $danosVeiculo->filter(fn ($dano) => (bool) ($dano['deve_cobrar'] ?? false))->count(),
            'danos_nao_cobrar' => $danosVeiculo->filter(fn ($dano) => in_array(($dano['status_revisao'] ?? ''), [
                VeiculoDanos::REVISAO_NAO_COBRAR,
                VeiculoDanos::REVISAO_JA_EXISTENTE,
                VeiculoDanos::REVISAO_DESGASTE_NORMAL,
            ], true))->count(),
        ];
    }

    private function mapearImagens(VeiculoDanos $dano): array
    {
        return collect($dano->imagem ?? $dano->imagens ?? collect())
            ->map(function ($imagem) {
                $path = $imagem->caminho_imagem ?? $imagem->path ?? null;

                return $path ? asset('storage/'.ltrim($path, '/')) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function isCobrancaPendente(?AvariaCobranca $cobranca): bool
    {
        return $cobranca
            && strtolower((string) $cobranca->status) === AvariaCobranca::STATUS_PENDENTE
            && strtolower((string) $cobranca->metodo) === AvariaCobranca::METODO_MANUAL;
    }

    private function isCobrancaEfetivada(?AvariaCobranca $cobranca): bool
    {
        return $this->isCobrancaEfetivadaStatus((string) ($cobranca?->status ?? ''));
    }

    private function isCobrancaEfetivadaStatus(string $status): bool
    {
        return in_array(strtolower($status), [
            AvariaCobranca::STATUS_COBRADO,
            AvariaCobranca::STATUS_PROCESSANDO,
        ], true);
    }

    private function isDanoSinalizado(array $dano): bool
    {
        return ! empty($dano['cobranca_pendente'])
            || ! empty($dano['ja_cobrado'])
            || ($dano['status'] ?? null) === VeiculoDanos::STATUS_DESCARTADA;
    }
}

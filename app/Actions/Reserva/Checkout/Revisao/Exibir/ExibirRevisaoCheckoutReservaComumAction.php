<?php

namespace App\Actions\Reserva\Checkout\Revisao\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Models\AvariaCobranca;
use App\Models\CategoriaDano;
use App\Models\VeiculoDanos;
use App\Services\BreadcrumbService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use RuntimeException;

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
                ->with('warning', 'Nenhum checkout encontrado para revisao.');
        }

        if (! $this->checkoutAssinado($checkout)) {
            throw new RuntimeException('Nao e possivel acessar a revisao do checkout porque o contrato ainda nao foi assinado.');
        }

        if (($registro->revisao_checkout_concluida ?? 'N') === 'S') {
            throw new RuntimeException('A revisao do checkout ja foi concluida para esta reserva.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$reserva->codigo, route('reserva.show', $reserva->codigo)),
            new BreadCrumbItem('Revisao do Check-out'),
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
            'titulo' => 'Revisao do Check-out - Reserva Nº '.$registro->id,
        ]);
    }

    private function checkoutAssinado($checkout): bool
    {
        return filled($checkout?->assinatura)
            || filled($checkout?->data_assinatura)
            || ($checkout?->assinado ?? 'N') === 'S';
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
            return 'Cobranca pendente';
        }

        if ($this->isCobrancaEfetivadaStatus($cobrancaStatus)) {
            return 'Cobranca confirmada';
        }

        return match ($dano->status ?? VeiculoDanos::STATUS_PENDENTE) {
            VeiculoDanos::STATUS_EM_CORRECAO => 'Em correcao',
            VeiculoDanos::STATUS_CORRIGIDA => 'Corrigido',
            VeiculoDanos::STATUS_PARCIALMENTE_CORRIGIDA => 'Parcialmente corrigido',
            VeiculoDanos::STATUS_DESCARTADA => 'Descartado',
            default => 'Pendente',
        };
    }

    private function obterMotivoCobranca(VeiculoDanos $dano, ?AvariaCobranca $cobranca, float $valorSugerido): string
    {
        if (($dano->status ?? null) === VeiculoDanos::STATUS_DESCARTADA) {
            return 'Este dano foi descartado e nao entra na cobranca.';
        }

        if ($this->isCobrancaPendente($cobranca)) {
            return 'Cobranca salva como pendente e editavel ate a finalizacao.';
        }

        if ($this->isCobrancaEfetivada($cobranca)) {
            return 'Este dano ja possui cobranca finalizada.';
        }

        if ($valorSugerido <= 0) {
            return 'Sem valor estimado para cobranca.';
        }

        return 'Dano com valor estimado e sem cobranca vinculada.';
    }

    private function calcularCobrancaTotal($danosRevisados): float
    {
        return (float) collect($danosRevisados)
            ->filter(fn ($dano) => (bool) ($dano['deve_cobrar'] ?? false))
            ->sum(fn ($dano) => (float) ($dano['valor_sugerido'] ?? 0));
    }

    private function montarResumoFinanceiro($registro, $danosVeiculo): array
    {
        return [
            'total_cobrancas' => $this->calcularCobrancaTotal($danosVeiculo),
        ];
    }

    private function isDanoSinalizado(array $dano): bool
    {
        return filled($dano['status_revisao'] ?? null) || filled($dano['cobranca_id'] ?? null);
    }

    private function isCobrancaPendente(?AvariaCobranca $cobranca): bool
    {
        if (! $cobranca) {
            return false;
        }

        return in_array((string) ($cobranca->status ?? ''), ['pendente', 'aguardando'], true);
    }

    private function isCobrancaEfetivada(?AvariaCobranca $cobranca): bool
    {
        if (! $cobranca) {
            return false;
        }

        return in_array((string) ($cobranca->status ?? ''), ['cobrado', 'pago'], true);
    }

    private function isCobrancaEfetivadaStatus(string $status): bool
    {
        return in_array($status, ['cobrado', 'pago'], true);
    }

    private function mapearImagens(VeiculoDanos $dano): array
    {
        return collect($dano->imagem ?? [])
            ->map(function ($imagem) {
                $path = trim((string) ($imagem->caminho_imagem ?? $imagem->nome_imagem ?? ''));

                if ($path === '') {
                    return null;
                }

                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                $path = ltrim($path, '/');

                if (str_starts_with($path, 'storage/')) {
                    return asset($path);
                }

                if (str_starts_with($path, 'public/')) {
                    return asset('storage/' . ltrim(substr($path, 7), '/'));
                }

                if (Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }

                return asset('storage/' . $path);
            })
            ->filter()
            ->values()
            ->all();
    }
}

<?php

namespace App\Actions\Reserva\Checkin\Veiculos;

use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\Services\VeiculoService;
use App\Services\ModeloService;
use App\Services\UpgradeVeiculoService;

class VeiculosModalCheckinAction
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService
    ) {}

    public function handle(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (!$contextoCheckin) {
            return response()->json(['message' => 'Reserva nao encontrada.'], 404);
        }

        $reserva = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];
        $dataInicio = \Carbon\Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim    = \Carbon\Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');
        $estacaoId  = (int) $reserva->estacao_retirada_id;

        $filtros = $request->only(['placa', 'nome', 'chassi', 'cor', 'modelo', 'marca_id', 'grupo_frota_id', 'tipo_frota_id', 'ativo', 'ano_de', 'ano_ate', 'kms_ate']);
        if (!empty($contextoCheckin['is_motorista_app'])) {
            $filtros['modelo_ids'] = $contextoCheckin['modelo_ids'];
            unset($filtros['grupo_frota_id']);
        }
        $filtrosVazios = collect($filtros)->filter(fn($v) => $v !== null && $v !== '')->isEmpty();

        if ($filtrosVazios) {
            $filtrosGrupo = [];
            if (!empty($reserva->grupo_frota_id)) {
                $filtrosGrupo['grupo_frota_id'] = $reserva->grupo_frota_id;
            }

            $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                $estacaoId,
                $dataInicio,
                $dataFim,
                (int) $reserva_id,
                $filtrosGrupo
            );

            if ($veiculos->isEmpty()) {
                $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                    $estacaoId,
                    $dataInicio,
                    $dataFim,
                    (int) $reserva_id,
                    []
                );
            }
        } else {
            $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                $estacaoId,
                $dataInicio,
                $dataFim,
                (int) $reserva_id,
                $filtros
            );
        }

        $toArray = static fn($v) => [
            'id'              => $v->id,
            'nome'            => $v->nome ?? '',
            'placa'           => $v->placa_licenca ?? '',
            'marca'           => $v->marca?->nome ?? '',
            'modelo'          => $v->modelo?->nome ?? '',
            'grupo'           => $v->grupoFrota?->nome ?? '',
            'tipo'            => $v->tipoFrota?->nome ?? '',
            'status'          => $v->statusVeiculo?->nome ?? '',
            'ano_fab'         => $v->ano_fabricacao ?? '',
            'ano_mod'         => $v->modelo?->ano_modelo ?? '',
            'cor'             => $v->cor ?? '',
            'kms'             => $v->kms ?? '',
            'ativo'           => $v->ativo === 'S' ? 'Sim' : 'Não',
            'estacao'         => '',
            'nivel_combustivel' => '',
        ];

        return response()->json(['veiculos' => $veiculos->map($toArray)->values()]);
    }
}

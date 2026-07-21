<?php

namespace App\Actions\Reserva\Checkin\Realizar;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\ContratoCheckin;
use App\Services\ContratoCheckinService;
use App\Services\ReservaService;
use App\Services\VeiculoDanoService;
use App\Services\VeiculoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RealizarCheckinReservaComumAction
{
    public function __construct(
        private ContratoCheckinService $contratoCheckinService,
        private VeiculoService $veiculoService,
        private ReservaService $reservaService,
        private VeiculoDanoService $veiculoDanoService,
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;
        $reserva_id = (int) $registro->id;

        $dados = $request->all();
        $dados['reserva_id'] = $reserva_id;

        // Deriva marca_id e modelo_id do veículo selecionado quando não enviados pelo formulário
        if (empty($dados['marca_id']) || empty($dados['modelo_id'])) {
            $veiculo = $this->veiculoService->getById((int) $dados['veiculo_id']);
            if ($veiculo) {
                $dados['marca_id']  = !empty($dados['marca_id']) ? (int) $dados['marca_id'] : (int) ($veiculo->marca_id ?: ($veiculo->modelo?->marca_id ?? 0));
                $dados['modelo_id'] = !empty($dados['modelo_id']) ? (int) $dados['modelo_id'] : (int) ($veiculo->modelo_id ?? 0);
            }
        }

        // Verifica se já existe contrato para esta reserva (modo edição)
        $contratoExistente = $this->contratoCheckinService->getModel()->newQuery()
            ->where('reserva_id', $reserva_id)
            ->first();

        try {
            // Salva as fotos do check-in no storage
            $dir = 'checkin/reserva_' . (int) $reserva_id;
            $map = [
                'foto_direita' => 'foto_direita_path',
                'foto_esquerda' => 'foto_esquerda_path',
                'foto_frente' => 'foto_frente_path',
                'foto_tras' => 'foto_tras_path',
                'foto_velocimetro' => 'foto_velocimetro_path',
                'foto_combustivel' => 'foto_combustivel_path',
            ];

            $destDir = storage_path('app/public/' . $dir);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0775, true);
            }

            foreach ($map as $input => $col) {
                if ($request->hasFile($input) && $request->file($input)->isValid()) {
                    if ($contratoExistente && !empty($contratoExistente->$col)) {
                        $oldPath = storage_path('app/public/' . $contratoExistente->$col);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $file      = $request->file($input);
                    $extension = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename  = Str::random(40) . '.' . $extension;
                    $file->move($destDir, $filename);
                    $dados[$col] = $dir . '/' . $filename;
                } elseif ($contratoExistente) {
                    $dados[$col] = $contratoExistente->$col;
                }
            }

            // Upgrade categoria check (if any)
            $this->resolverDadosUpgrade($dados, $reserva_id, $registro);

            if ($contratoExistente) {
                $contratoExistente->update($dados);
                $this->sincronizarCheckinMotoristaApp($reserva, $contratoExistente->fresh(), $dados);
                $this->reservaService->adicionaLog($reserva_id, Auth::id(), [
                    'houve_upgrade'  => $dados['houve_upgrade_categoria'] ?? false,
                    'motivo_upgrade' => $dados['motivo_upgrade'] ?? null,
                    'veiculo_id'     => $dados['veiculo_id'] ?? null,
                ], 'Contrato de check-in editado', 'info');

                return response()->json($contratoExistente->fresh());
            } else {
                $this->reservaService->adicionaLog($reserva_id, Auth::id(), [
                    'houve_upgrade'  => $dados['houve_upgrade_categoria'] ?? false,
                    'motivo_upgrade' => $dados['motivo_upgrade'] ?? null,
                    'veiculo_id'     => $dados['veiculo_id'] ?? null,
                ], 'Contrato de check-in criado', 'success');

                $contrato = $this->contratoCheckinService->create($dados);
                $this->sincronizarCheckinMotoristaApp($reserva, $contrato, $dados);

                return response()->json($contrato);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao realizar checkin: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function resolverDadosUpgrade(array &$dados, int $reserva_id, $reserva): void
    {
        $veiculo = $this->veiculoService->getById((int) $dados['veiculo_id']);
        $novaCategoriaId = (int) ($veiculo->modelo?->categoria_id ?? 0);
        $categoriaOriginalId = (int) ($reserva->grupoFrota?->categoria_id ?? $reserva->planoMotoristaApp?->grupoFrota?->categoria_id ?? 0);

        $dados['houve_upgrade_categoria'] = false;
        $dados['categoria_upgrade_id'] = null;

        if ($novaCategoriaId > 0 && $categoriaOriginalId > 0 && $novaCategoriaId !== $categoriaOriginalId) {
            $dados['houve_upgrade_categoria'] = true;
            $dados['categoria_upgrade_id'] = $novaCategoriaId;
            $dados['usuario_upgrade_id'] = Auth::id();
        }
    }

    private function sincronizarCheckinMotoristaApp(ReservaResolvida $reserva, ?ContratoCheckin $contrato, array $dados): void
    {
        if (!$contrato) {
            return;
        }

        $motoristaApp = null;
        if ($reserva->ehMotoristaApp()) {
            /** @var \App\Models\ReservaMotoristaApp $motoristaApp */
            $motoristaApp = $reserva->registro;
        } else {
            $motoristaApp = \App\Models\ReservaMotoristaApp::whereHas('codigoReserva', function ($q) use ($reserva) {
                $q->where('reserva_id', (int) $reserva->registro->id);
            })
            ->orWhere('id', (int) $reserva->registro->id)
            ->first();
        }

        if (!$motoristaApp) {
            return;
        }

        $documentos = is_array($motoristaApp->documentos_json ?? null) ? $motoristaApp->documentos_json : [];
        $reservaOperacionalId = (int) $reserva->registro->id;

        data_set($documentos, 'checkin.reserva_operacional_id', $reservaOperacionalId);
        data_set($documentos, 'checkin.reserva_operacional_codigo', $reserva->codigo);
        data_set($documentos, 'checkin.contrato_checkin_id', (int) $contrato->id);
        data_set($documentos, 'checkin.veiculo_id', (int) ($dados['veiculo_id'] ?? $contrato->veiculo_id));
        data_set($documentos, 'checkin.quilometragem_inicial', $dados['quilometragem_atual'] ?? $contrato->quilometragem_atual);
        data_set($documentos, 'checkin.nivel_combustivel_inicial', $dados['nivel_combustivel'] ?? $contrato->nivel_combustivel);
        data_set($documentos, 'checkin.registrado_em', optional($contrato->created_at ?: now())->toDateTimeString());

        $checkinEm = $motoristaApp->checkin_realizado_em ?: now();

        $motoristaApp->forceFill([
            'veiculo_id' => (int) ($dados['veiculo_id'] ?? $contrato->veiculo_id),
            'checkin_realizado_em' => $checkinEm,
            'data_retirada_real' => $motoristaApp->data_retirada_real ?: $checkinEm,
            'quilometragem_inicial' => $dados['quilometragem_atual'] ?? $contrato->quilometragem_atual,
            'nivel_combustivel_inicial' => $dados['nivel_combustivel'] ?? $contrato->nivel_combustivel,
            'documentos_json' => $documentos,
        ])->save();
    }
}

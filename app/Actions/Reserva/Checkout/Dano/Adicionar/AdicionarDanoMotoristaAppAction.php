<?php

namespace App\Actions\Reserva\Checkout\Dano\Adicionar;

use App\Models\VeiculoDanos;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdicionarDanoMotoristaAppAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(array $dados, $reservaCodigoId, Request $request): mixed
    {
        $dados['reserva_codigo_id'] = $reservaCodigoId;
        $fotos = $request->file('fotos', []);
        unset($dados['fotos']);

        try {
            DB::beginTransaction();

            $dano = VeiculoDanos::create($dados);

            if ($dano && !empty($fotos)) {
                $dir = 'danos/reserva_codigo_' . (int) $reservaCodigoId . '/dano_' . (int) $dano->id;

                foreach ($fotos as $foto) {
                    $path = \App\Support\ArquivoAnexoHelper::armazenar($foto, $dir);
                    if (!$path) continue;

                    $dano->imagem()->create([
                        'caminho_imagem' => $path,
                        'nome_imagem'    => basename($path),
                    ]);
                }
            }

            $this->reservaService->adicionaLog($dados['veiculo_id'] ?? 0, Auth::id(), json_encode([
                'acao'        => 'Dano de veiculo adicionado (Motorista App)',
                'dano_id'     => $dano?->id,
                'reserva_codigo_id' => $reservaCodigoId,
                'veiculo_id'  => $dados['veiculo_id'] ?? null,
                'categoria_id'=> $dados['categoria_id'] ?? null,
                'local_dano'  => $dados['local_dano'] ?? null,
                'tipo_dano'   => $dados['tipo_dano'] ?? null,
                'fotos'       => is_array($fotos) ? count($fotos) : 0,
            ]));

            DB::commit();

            return $dano?->load('imagem');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

<?php

namespace App\Actions\Reserva\Checkout\Dano\ObterDetectados;

use App\Models\VeiculoDanos;
use Illuminate\Http\JsonResponse;

class ObterDanosDetectadosReservaComumAction
{
    public function handle(int $veiculoId, int $reservaCodigoId): JsonResponse
    {
        $query = VeiculoDanos::where('veiculo_id', $veiculoId)
            ->where('reserva_codigo_id', $reservaCodigoId);

        if (!$query->exists()) {
            // Se não encontrou pelo código intermediário, busca direto pela reserva vinculada a este código
            $reservaId = \App\Models\ReservaCodigo::where('id', $reservaCodigoId)->value('reserva_id');
            if ($reservaId) {
                $query = VeiculoDanos::where('veiculo_id', $veiculoId)
                    ->where('reserva_id', $reservaId);
            }
        }

        $danos = $query->with(['imagens', 'dano.categoria', 'usuarioCorrecao'])
            ->orderBy('id')
            ->get()
            ->map(fn(VeiculoDanos $d) => $this->mapear($d))
            ->values()
            ->all();

        return response()->json($danos);
    }

    private function mapear(VeiculoDanos $d): array
    {
        $loc = $d->local_dano === 'atras' ? 'ATRÁS' : strtoupper((string) $d->local_dano);

        $imagens = $d->imagens->map(function ($img) {
            $path = $img->caminho_imagem ?: $img->nome_imagem;
            $url = null;
            if (!empty($path)) {
                try {
                    $normalizedPath = ltrim(str_replace('\\', '/', (string) $path), '/');
                    $url = asset('storage/' . $normalizedPath);
                } catch (\Throwable $e) {
                    $url = null;
                }
            }
            return [
                'nome' => $img->nome_imagem,
                'path' => $img->caminho_imagem,
                'url'  => $url,
            ];
        })->values()->all();

        return [
            'id'                   => $d->id,
            'localizacao'          => $loc,
            'descricao'            => $d->observacoes,
            'tipo_dano'            => $d->tipo_dano,
            'nome_dano'            => $d->dano?->nome,
            'categoria'            => $d->dano?->categoria?->nome,
            'local_dano'           => $d->local_dano,
            'localizacao_problema' => $d->localizacao_problema,
            'custo_estimado'       => $d->custo_estimado,
            'quilometragem_veiculo'=> $d->quilometragem_veiculo,
            'created_at'           => $d->created_at?->format('d/m/Y H:i'),
            'imagens'              => $imagens,
            'correcao_descricao'   => $d->correcao_descricao,
            'correcao_data'        => $d->correcao_data?->format('d/m/Y H:i'),
            'correcao_usuario'     => $d->usuarioCorrecao?->nome,
            'correcao_realizada'   => $d->correcao_data !== null,
        ];
    }
}

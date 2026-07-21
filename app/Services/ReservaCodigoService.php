<?php

namespace App\Services;

use App\Models\ReservaCodigo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservaCodigoService
{
    public function sincronizar(Model $registro, string $tipoOrigem, array $dados = []): ReservaCodigo
    {
        $chaveBusca = $tipoOrigem === 'motorista_app'
            ? ['reserva_motorista_app_id' => (int) $registro->id]
            : ['reserva_id' => (int) $registro->id];

        $empresaId = array_key_exists('empresa_id', $dados)
            ? ($dados['empresa_id'] !== null && $dados['empresa_id'] !== '' ? (int) $dados['empresa_id'] : null)
            : (isset($registro->empresa_id) && $registro->empresa_id !== null ? (int) $registro->empresa_id : null);

        $payload = [
            'empresa_id' => $empresaId,
            'tipo_origem' => $tipoOrigem,
            'status_label' => $dados['status_label'] ?? null,
            'data_inicio' => $this->normalizarData($dados['data_inicio'] ?? ($registro->data_inicio ?? null)),
            'data_fim' => $this->normalizarData($dados['data_fim'] ?? ($registro->data_fim ?? null)),
            'valor_total' => (float) ($dados['valor_total'] ?? ($registro->valor_total_final ?? $registro->valor_total ?? $registro->valor_plano ?? 0)),
        ];

        if ($tipoOrigem === 'motorista_app') {
            $payload['reserva_motorista_app_id'] = (int) $registro->id;
        } else {
            $payload['reserva_id'] = (int) $registro->id;
        }

        $existente = ReservaCodigo::query()->where($chaveBusca)->first();
        if ($existente) {
            $existente->fill($payload);
            $existente->save();

            return $existente->refresh();
        }

        $payload['codigo'] = $this->gerarCodigo($tipoOrigem, (int) $registro->id);
        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        DB::table('reserva_codigos')->updateOrInsert($chaveBusca, $payload);

        return ReservaCodigo::query()->where($chaveBusca)->firstOrFail();
    }

    public function gerarCodigo(string $tipoOrigem, ?int $registroId = null): string
    {
        $prefixo = $tipoOrigem === 'motorista_app' ? 'MAP' : 'RES';

        if ($registroId && $registroId > 0) {
            $codigo = sprintf('%s-%s-%06d', $prefixo, now()->format('ymd'), $registroId);
            if (!ReservaCodigo::query()->where('codigo', $codigo)->exists()) {
                return $codigo;
            }
        }

        do {
            $codigo = sprintf('%s-%s-%s', $prefixo, now()->format('ymd'), strtoupper(Str::random(5)));
        } while (ReservaCodigo::query()->where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function normalizarData(mixed $valor): ?Carbon
    {
        if ($valor instanceof Carbon) {
            return $valor;
        }

        if (empty($valor)) {
            return null;
        }

        try {
            return Carbon::parse($valor);
        } catch (\Throwable) {
            return null;
        }
    }
}

<?php

namespace App\Services\Reserva;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservaIdentificadorService
{
    public function dadosCodigoReservaPorIdentificador(string|int $identificador): ?object
    {
        $codigo = strtoupper(trim((string) $identificador));

        if ($codigo === '' || ctype_digit($codigo) || !Schema::hasTable('reserva_codigos')) {
            return null;
        }

        return DB::table('reserva_codigos')
            ->whereRaw('UPPER(codigo) = ?', [$codigo])
            ->first();
    }

    public function idReservaNumericoDoCodigoLegado(string|int $identificador, string $prefixo): ?int
    {
        $codigo = strtoupper(trim((string) $identificador));
        $pattern = '/^' . preg_quote(strtoupper($prefixo), '/') . '-\d{6}-(\d{6,})$/';

        if (!preg_match($pattern, $codigo, $matches)) {
            return null;
        }

        return (int) ltrim($matches[1], '0') ?: null;
    }

    public function idReservaPorIdentificador(string|int $identificador, string $prefixo, ?object $codigoReserva, string $campoCodigo): string|int
    {
        if ($codigoReserva && (int) ($codigoReserva->{$campoCodigo} ?? 0) > 0) {
            return (int) $codigoReserva->{$campoCodigo};
        }

        if (!ctype_digit((string) $identificador)) {
            return $this->idReservaNumericoDoCodigoLegado($identificador, $prefixo) ?? $identificador;
        }

        return $identificador;
    }

    public function identificadorComecaComPrefixo(string|int $identificador, string $prefixo): bool
    {
        return str_starts_with(strtoupper(trim((string) $identificador)), strtoupper($prefixo) . '-');
    }

    public function codigoReservaOperacionalPorId(int $reservaId): ?string
    {
        if (!Schema::hasTable('reserva_codigos')) {
            return null;
        }

        return DB::table('reserva_codigos')
            ->where('reserva_id', $reservaId)
            ->orderBy('id')
            ->value('codigo') ?: null;
    }

    public function resolverPorCodigo(string|int $codigo, string $model = 'reserva'): string|int
    {
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);

        if ($model === 'motorista_app') {
            $id = $this->idReservaPorIdentificador($codigo, 'MAP', $codigoReserva, 'reserva_motorista_app_id');
            if (!is_numeric($id)) {
                $id = $this->idReservaNumericoDoCodigoLegado($codigo, 'MAP') ?? $id;
            }
            return $id;
        }

        $id = $this->idReservaPorIdentificador($codigo, 'RES', $codigoReserva, 'reserva_id');
        if (!is_numeric($id)) {
            $id = $this->idReservaNumericoDoCodigoLegado($codigo, 'MAP') ?? $id;
        }
        return $id;
    }
}

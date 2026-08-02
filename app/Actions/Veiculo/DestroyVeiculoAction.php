<?php

namespace App\Actions\Veiculo;

use App\Enums\StatusReservaEnum;
use App\Models\Reserva;
use App\Models\StatusVeiculo;
use App\Services\VeiculoService;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DestroyVeiculoAction
{
    public function __construct(private readonly VeiculoService $veiculoService) {}

    public function handle($id, string $nomeFiltro)
    {
        $veiculo = $this->veiculoService->getById($id);

        if (! $veiculo) {
            return redirect()
                ->route($nomeFiltro.'.index')
                ->with('error', 'Veículo não encontrado.');
        }

        $reservasBloqueadoras = Reserva::query()
            ->where('veiculo_id', (int) $veiculo->id)
            ->whereIn('status_reserva_id', [
                StatusReservaEnum::Pendente->value,
                StatusReservaEnum::Confirmada->value,
                StatusReservaEnum::Checkin->value,
                StatusReservaEnum::Checkout->value,
            ])
            ->where(function ($query) {
                $query->whereNull('data_fim')
                    ->orWhere('data_fim', '>=', now());
            })
            ->with('codigoReserva:id,reserva_id,codigo')
            ->orderBy('data_inicio')
            ->get();

        if ($reservasBloqueadoras->isNotEmpty()) {
            $codigos = $reservasBloqueadoras
                ->map(fn ($reserva) => $reserva->codigoReserva?->codigo ?? ('#'.$reserva->id))
                ->implode(', ');

            return redirect()
                ->route($nomeFiltro.'.index')
                ->with('error', 'O veículo não pode ser arquivado porque possui reservas futuras ou em andamento: '.$codigos.'. Troque o veículo ou finalize/cancele essas reservas primeiro.');
        }

        DB::transaction(function () use ($veiculo) {
            $dadosAtualizacao = [];

            if (SchemaCache::hasColumn('veiculos', 'ativo')) {
                $dadosAtualizacao['ativo'] = 0;
            }
            if (SchemaCache::hasColumn('veiculos', 'arquivado_em')) {
                $dadosAtualizacao['arquivado_em'] = now();
            }
            if (SchemaCache::hasColumn('veiculos', 'arquivado_por')) {
                $dadosAtualizacao['arquivado_por'] = Auth::id();
            }
            if (SchemaCache::hasColumn('veiculos', 'situacao_cadastral')) {
                $dadosAtualizacao['situacao_cadastral'] = 'arquivado';
            }
            if (SchemaCache::hasColumn('veiculos', 'status_veiculo_id')) {
                $statusArquivadoId = StatusVeiculo::query()
                    ->where(function ($query) {
                        $query->whereRaw('LOWER(nome) = ?', ['arquivado'])
                            ->orWhereRaw('LOWER(nome) = ?', ['inativo']);
                    })
                    ->value('id');
                if ($statusArquivadoId) {
                    $dadosAtualizacao['status_veiculo_id'] = $statusArquivadoId;
                }
            }

            if (! empty($dadosAtualizacao)) {
                DB::table('veiculos')->where('id', (int) $veiculo->id)->update($dadosAtualizacao);
            }
        });

        return redirect()->route($nomeFiltro.'.index')->with('success', 'Veículo arquivado com sucesso.');
    }
}

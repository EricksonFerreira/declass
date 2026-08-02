<?php

namespace App\Http\Controllers\Reserva;

use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;
use App\Models\ContratoCheckin;
use App\Models\ReservaCodigo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

trait ReservaHelpersTrait
{
    private function resolverDadosUpgrade(array &$dados, int $reservaId, ?Reserva $reservaContexto = null): void
    {
        $reserva = $reservaContexto ?: $this->reservaService->getById($reservaId, ['grupoFrota']);
        if (!$reserva) {
            return;
        }

        $veiculoId = (int) ($dados['veiculo_id'] ?? 0);
        if (!$veiculoId) {
            return;
        }

        $veiculo = $this->veiculoService->getById($veiculoId);
        if (!$veiculo) {
            return;
        }

        // Registra grupo original sempre
        $dados['grupo_frota_original_id'] = $reserva->grupo_frota_id;

        // Registra veiculo previsto, se existir na reserva
        if (!empty($reserva->veiculo_id)) {
            $dados['veiculo_previsto_id'] = (int) $reserva->veiculo_id;
        }

        // Blindagem: veiculo deve estar na estacao de retirada (quando aplicavel)
        if (!empty($reserva->estacao_retirada_id) && !empty($veiculo->local_disponibilidade)) {
            if ((int) $veiculo->local_disponibilidade !== (int) $reserva->estacao_retirada_id) {
                throw ValidationException::withMessages([
                    'veiculo_id' => 'Veículo selecionado não esta disponível na estacão de retirada desta reserva.',
                ]);
            }
        }

        if (!$reserva->grupo_frota_id) {
            return;
        }

        // Mesmo grupo: sem upgrade
        if ((int) $veiculo->grupo_frota_id === (int) $reserva->grupo_frota_id) {
            return;
        }

        // Categoria original ainda tem disponibilidade ➔ aplica regras de upgrade/downgrade
        $categoriaDisponivel = $this->upgradeVeiculoService->categoriaOriginalTemDisponibilidade($reserva);

        if ($categoriaDisponivel) {
            // Há veículos na categoria original: só permite upgrade (nunca downgrade)
            $ehUpgrade = $this->upgradeVeiculoService->veiculoElegivelParaUpgrade($veiculo, $reserva);
            if (!$ehUpgrade) {
                throw ValidationException::withMessages([
                    'veiculo_id' => 'Nao e permitido selecionar veiculo de categoria inferior a reservada (downgrade bloqueado).',
                ]);
            }
        }

        // Categoria original sem disponibilidade (ou é upgrade): registra como upgrade
        $motivo = trim((string) ($dados['motivo_upgrade'] ?? ''));

        $dados['houve_upgrade_categoria'] = true;
        $dados['motivo_upgrade']          = $motivo !== '' ? $motivo : 'Indisponibilidade na categoria original';
        $dados['usuario_upgrade_id']      = Auth::id();
        $dados['data_hora_upgrade']       = now();
    }
    private function dadosCodigoReservaPorIdentificador(string|int $id): ?object
    {
        return app(\App\Services\Reserva\ReservaIdentificadorService::class)
            ->dadosCodigoReservaPorIdentificador($id);
    }

    private function identificadorComecaComPrefixo(string|int $identificador, string $prefixo): bool
    {
        return str_starts_with(strtoupper(trim((string) $identificador)), strtoupper($prefixo) . '-');
    }

    private function idReservaNumericoDoCodigoLegado(string|int $identificador, string $prefixo): ?int
    {
        $codigo = strtoupper(trim((string) $identificador));
        $pattern = '/^' . preg_quote(strtoupper($prefixo), '/') . '-\d{6}-(\d{6,})$/';

        if (!preg_match($pattern, $codigo, $matches)) {
            return null;
        }

        return (int) ltrim($matches[1], '0') ?: null;
    }

    private function idReservaPorIdentificador(string|int $identificador, string $prefixo, ?object $codigoReserva, string $campoCodigo): string|int
    {
        if ($codigoReserva && (int) ($codigoReserva->{$campoCodigo} ?? 0) > 0) {
            return (int) $codigoReserva->{$campoCodigo};
        }

        if (!ctype_digit((string) $identificador)) {
            return $this->idReservaNumericoDoCodigoLegado($identificador, $prefixo) ?? $identificador;
        }

        return $identificador;
    }

    private function codigoReservaOperacionalPorId(int $reservaId): ?string
    {
        if ($reservaId <= 0) {
            return null;
        }

        return DB::table('reserva_codigos')
            ->where('reserva_id', $reservaId)
            ->orderBy('id')
            ->value('codigo') ?: null;
    }

    private function resolverReservaOperacionalPorCodigo(string|int $codigo): ?Reserva
    {
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);
        $reservaId = $this->idReservaPorIdentificador($codigo, 'RES', $codigoReserva, 'reserva_id');

        if (!is_numeric($reservaId)) {
            $reservaId = $this->idReservaNumericoDoCodigoLegado($codigo, 'MAP');
        }

        if (!is_numeric($reservaId)) {
            return null;
        }

        return Reserva::withoutGlobalScopes()
            ->with(['codigoReserva'])
            ->find((int) $reservaId);
    }

    private function checkinMotoristaAppAssinado(ReservaMotoristaApp $registro): bool
    {
        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
        $contratoId = (int) data_get($documentos, 'checkin.contrato_checkin_id', 0);

        if ($contratoId <= 0) {
            return false;
        }

        return ContratoCheckin::where('id', $contratoId)
            ->where('assinado', 'S')
            ->exists();
    }

    private function modeloIdsDisponiveisMotoristaApp(ReservaMotoristaApp $registro): array
    {
        if (empty($registro->estacao_retirada_id) || empty($registro->data_inicio) || empty($registro->data_fim)) {
            return [];
        }

        $filtros = [];
        $registro->loadMissing('planoMotoristaApp.gruposFrota');
        if ($registro->planoMotoristaApp) {
            $grupoFrotaIds = $registro->planoMotoristaApp->gruposFrota->pluck('id')->all();
            if (!empty($grupoFrotaIds)) {
                $filtros['grupo_frota_ids'] = $grupoFrotaIds;
            }
        }

        return $this->veiculoService
            ->buscarDisponiveisCheckin(
                (int) $registro->estacao_retirada_id,
                Carbon::parse($registro->data_inicio)->format('Y-m-d H:i:s'),
                Carbon::parse($registro->data_fim)->format('Y-m-d H:i:s'),
                0,
                $filtros
            )
            ->pluck('modelo_id')
            ->map(static fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function contextoCheckinPorCodigo(string|int $codigo): ?array
    {
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);

        if ($codigoReserva && $codigoReserva->tipo_origem === 'motorista_app') {
            $registro = ReservaMotoristaApp::withoutGlobalScopes()
                ->with(['codigoReserva', 'user', 'empresa', 'estacaoRetirada', 'planoMotoristaApp'])
                ->find((int) ($codigoReserva->reserva_motorista_app_id ?? 0));

            if (!$registro) {
                return null;
            }

            $documentosMotoristaApp = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
            $reservaOperacionalId = (int) data_get($documentosMotoristaApp, 'checkin.reserva_operacional_id', 0);

            if ($reservaOperacionalId <= 0) {
                $reservaOperacionalId = (int) (DB::table('reservas')
                    ->join('reserva_codigos', 'reserva_codigos.reserva_id', '=', 'reservas.id')
                    ->leftJoin('contrato_checkin', 'contrato_checkin.reserva_id', '=', 'reservas.id')
                    ->where('reservas.user_id', (int) $registro->user_id)
                    ->where('reservas.empresa_id', (int) $registro->empresa_id)
                    ->whereNull('contrato_checkin.id')
                    ->whereRaw('LOWER(COALESCE(reserva_codigos.status_label, "")) = ?', ['pendente'])
                    ->orderByDesc('reservas.id')
                    ->value('reservas.id') ?? 0);
            }

            $modeloIds = $this->modeloIdsDisponiveisMotoristaApp($registro);
            $modeloPrincipal = !empty($modeloIds)
                ? $this->modeloService->getAtivosPorIds($modeloIds)->first()
                : null;

            $reserva = new Reserva();
            $reserva->id = $reservaOperacionalId;
            $reserva->exists = false;
            $reserva->empresa_id = $registro->empresa_id;
            $reserva->user_id = $registro->user_id;
            $reserva->estacao_retirada_id = $registro->estacao_retirada_id;
            $reserva->estacao_devolucao_id = $registro->estacao_retirada_id;
            $reserva->data_inicio = $registro->data_inicio;
            $reserva->data_fim = $registro->data_fim;
            $reserva->quantidade_dias = $registro->quantidade_dias;
            $statusMotoristaApp = strtolower((string) ($registro->status ?? ''));
            $reserva->pagamento_efetuado = ($registro->pagamento_efetuado || in_array($statusMotoristaApp, ['confirmada', 'ativa'], true)) ? 'S' : 'N';
            $reserva->valor_total_final = $registro->valor_total_final;
            $reserva->grupo_frota_id = null;
            $reserva->modelo_id = $modeloPrincipal?->id;
            $reserva->setRelation('usuario', $registro->user);
            $reserva->setRelation('empresa', $registro->empresa);
            $reserva->setRelation('estacaoRetirada', $registro->estacaoRetirada);
            $reserva->setRelation('modelos', $modeloPrincipal);
            $reserva->setRelation('codigoReserva', new ReservaCodigo([
                'codigo' => (string) $codigoReserva->codigo,
                'reserva_motorista_app_id' => (int) $registro->id,
            ]));

            return [
                'reserva' => $reserva,
                'codigo_reserva' => (string) $codigoReserva->codigo,
                'reserva_id' => $reservaOperacionalId,
                'motorista_app' => $registro,
                'is_motorista_app' => true,
                'modelo_ids' => $modeloIds,
                'grupo_label' => 'Motorista APP - ' . ($registro->planoMotoristaApp?->nome ?? 'Plano'),
            ];
        }

        $reservaId = $this->idReservaPorIdentificador($codigo, 'RES', $codigoReserva, 'reserva_id');
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['modelos', 'codigoReserva', 'usuario', 'estacaoRetirada', 'grupoFrota'])
            ->find($reservaId);

        if (!$reserva) {
            return null;
        }

        return [
            'reserva' => $reserva,
            'codigo_reserva' => $codigoReserva?->codigo ?? $reserva->codigoReserva?->codigo ?? (string) $codigo,
            'reserva_id' => (int) $reserva->id,
            'motorista_app' => null,
            'is_motorista_app' => false,
            'modelo_ids' => $this->veiculoService->getModeloIdsDeVeiculosNoGrupo((int) $reserva->grupo_frota_id),
            'grupo_label' => $reserva->grupoFrota?->nome ?? ($reserva->grupo_frota_id ? 'Grupo #' . $reserva->grupo_frota_id : '---'),
        ];
    }

    private function sincronizarCheckinMotoristaApp(array $contextoCheckin, ?ContratoCheckin $contrato, array $dados): void
    {
        if (empty($contextoCheckin['is_motorista_app']) || !$contrato) {
            return;
        }

        /** @var ReservaMotoristaApp|null $registro */
        $registro = $contextoCheckin['motorista_app'] ?? null;
        if (!$registro) {
            return;
        }

        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
        $reservaOperacionalId = (int) $contextoCheckin['reserva_id'];
        data_set($documentos, 'checkin.reserva_operacional_id', $reservaOperacionalId);
        data_set($documentos, 'checkin.reserva_operacional_codigo', $this->codigoReservaOperacionalPorId($reservaOperacionalId));
        data_set($documentos, 'checkin.contrato_checkin_id', (int) $contrato->id);
        data_set($documentos, 'checkin.veiculo_id', (int) ($dados['veiculo_id'] ?? $contrato->veiculo_id));
        data_set($documentos, 'checkin.quilometragem_inicial', $dados['quilometragem_atual'] ?? $contrato->quilometragem_atual);
        data_set($documentos, 'checkin.nivel_combustivel_inicial', $dados['nivel_combustivel'] ?? $contrato->nivel_combustivel);
        data_set($documentos, 'checkin.registrado_em', optional($contrato->created_at ?: now())->toDateTimeString());

        $checkinEm = $registro->checkin_realizado_em ?: now();
        $statusAtual = strtolower((string) ($registro->status ?? ''));

        $registro->forceFill([
            'veiculo_id' => (int) ($dados['veiculo_id'] ?? $contrato->veiculo_id),
            'checkin_realizado_em' => $checkinEm,
            'data_retirada_real' => $registro->data_retirada_real ?: $checkinEm,
            'quilometragem_inicial' => $dados['quilometragem_atual'] ?? $contrato->quilometragem_atual,
            'nivel_combustivel_inicial' => $dados['nivel_combustivel'] ?? $contrato->nivel_combustivel,
            'documentos_json' => $documentos,
            'status' => $statusAtual === 'cancelada' ? $registro->status : 'ativa',
        ])->save();
    }

    /**
     * Helper centralizado para salvar arquivos (PDFs, imagens enviadas ou imagens base64).
     */
    private function salvarArquivo(mixed $arquivo, string $diretorio, ?string $nomePersonalizado = null): ?string
    {
        if (empty($arquivo)) {
            return null;
        }

        // Garante que o diretório exista
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($diretorio)) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($diretorio);
        }

        // Caso 1: String base64 (ex: assinatura em canvas)
        if (is_string($arquivo) && preg_match('/^data:image\/(\w+);base64,/', $arquivo, $type)) {
            $base64 = substr($arquivo, strpos($arquivo, ',') + 1);
            $dadosBinarios = base64_decode($base64);
            $extensao = $type[1] ?? 'png';
            $nomeFinal = ($nomePersonalizado ?: \Illuminate\Support\Str::random(10)) . '.' . $extensao;
            $caminhoRelativo = $diretorio . '/' . $nomeFinal;
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($caminhoRelativo, $dadosBinarios);
            return $caminhoRelativo;
        }

        // Caso 2: Upload de arquivo (UploadedFile)
        if (is_object($arquivo) && method_exists($arquivo, 'isValid') && $arquivo->isValid()) {
            $extensao = method_exists($arquivo, 'getClientOriginalExtension') 
                ? $arquivo->getClientOriginalExtension() 
                : (method_exists($arquivo, 'extension') ? $arquivo->extension() : 'bin');

            $nomeFinal = $nomePersonalizado 
                ? $nomePersonalizado . '.' . ($extensao ?: 'bin')
                : \Illuminate\Support\Str::random(40) . '.' . ($extensao ?: 'bin');
            
            $caminhoRelativo = $diretorio . '/' . $nomeFinal;

            // Salvamento direto usando PHP Nativo no diretório correspondente da pasta public
            $caminhoPublicoDestino = public_path('storage/' . $diretorio);
            if (!file_exists($caminhoPublicoDestino)) {
                mkdir($caminhoPublicoDestino, 0755, true);
            }

            $caminhoFisicoCompleto = $caminhoPublicoDestino . '/' . $nomeFinal;
            
            // Tenta mover de forma nativa
            if (method_exists($arquivo, 'move')) {
                $arquivo->move($caminhoPublicoDestino, $nomeFinal);
            } else {
                $caminhoRealTemp = method_exists($arquivo, 'getRealPath') ? $arquivo->getRealPath() : $arquivo->getPathname();
                copy($caminhoRealTemp, $caminhoFisicoCompleto);
            }

            return $caminhoRelativo;
        }

        return null;
    }
}

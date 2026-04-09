<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Estacao;
use App\Models\Nfse;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\Nfse\Providers\NfephpProvider;
use App\Services\Nfse\Providers\NfseProviderInterface;
use App\Services\Nfse\Providers\NuvemFiscalProvider;

class NfseService
{
    public function mensagemErro(?Nfse $nfse): ?string
    {
        if (!$nfse) {
            return null;
        }

        $response = $nfse->response ?? null;
        if (is_string($response)) {
            return trim($response) !== '' ? $response : null;
        }

        if (!is_array($response)) {
            return null;
        }

        $message = $response['message'] ?? $response['erro'] ?? $response['error'] ?? null;
        if (is_string($message) && trim($message) !== '') {
            return $message;
        }

        $errors = $response['errors'] ?? null;
        if (is_array($errors) && !empty($errors)) {
            $flat = array_filter(array_map(static function ($item) {
                if (is_string($item)) {
                    return $item;
                }
                if (is_array($item)) {
                    return implode(' ', array_map('strval', $item));
                }
                return null;
            }, $errors));

            if (!empty($flat)) {
                return implode(' ', $flat);
            }
        }

        return null;
    }

    public function emitirParaReserva(Reserva $reserva): ?Nfse
    {
        $empresa = $this->resolveEmpresa($reserva);
        if (!$empresa || ($empresa->nfse_ativo ?? 'N') !== 'S') {
            return null;
        }

        $latest = Nfse::query()
            ->where('reserva_id', $reserva->id)
            ->orderByDesc('id')
            ->first();

        if ($latest && $latest->status === 'emitida') {
            return $latest;
        }

        if ($latest && $latest->status === 'cancelada') {
            $nfse = Nfse::query()->create([
                'empresa_id' => $empresa->id,
                'reserva_id' => $reserva->id,
                'status' => 'pendente',
                'referencia' => $this->referenciaReserva($reserva, true),
                'ambiente' => $this->ambiente($empresa),
            ]);
        } else {
            $nfse = $latest ?: Nfse::query()->create([
                'empresa_id' => $empresa->id,
                'reserva_id' => $reserva->id,
                'status' => 'pendente',
                'referencia' => $this->referenciaReserva($reserva),
                'ambiente' => $this->ambiente($empresa),
            ]);
        }

        $payload = $this->buildPayload($empresa, $reserva);
        $nfse->payload = $payload['payload'] ?? null;

        if (!empty($payload['errors'])) {
            $nfse->status = 'erro';
            $nfse->response = ['message' => 'Configuracao incompleta para NFS-e.', 'errors' => $payload['errors']];
            $nfse->save();
            return $nfse;
        }

        $provider = $this->resolveProvider($empresa);
        $result = $provider->emitir($empresa, $payload['payload']);
        $data = $result['response'] ?? null;

        if (($result['status'] ?? 'error') !== 'success') {
            $nfse->status = 'erro';
            $nfse->response = $data ?: ['message' => 'Falha ao emitir NFS-e.'];
            $nfse->save();
            return $nfse;
        }

        $nfse->nfse_id = $data['id'] ?? $data['nfse_id'] ?? ($data['nfse']['id'] ?? null);
        $nfse->dps_id = $data['dps_id'] ?? ($data['dps']['id'] ?? null) ?? $nfse->dps_id;
        $nfse->numero = $data['numero'] ?? ($data['nfse']['numero'] ?? null) ?? $nfse->numero;
        $nfse->serie = $data['serie'] ?? ($data['nfse']['serie'] ?? null) ?? $nfse->serie;
        $normalized = $this->normalizarStatus($data);
        if ($normalized) {
            $nfse->status = $normalized;
        } elseif (empty($nfse->nfse_id) && !empty($nfse->dps_id)) {
            $nfse->status = 'processando';
        } else {
            $nfse->status = 'emitida';
        }
        $nfse->response = $data;
        $nfse->save();

        return $nfse;
    }

    public function cancelarPorReserva(Reserva $reserva, string $motivo = 'Cancelamento da reserva'): ?Nfse
    {
        $empresa = $this->resolveEmpresa($reserva);
        if (!$empresa || ($empresa->nfse_ativo ?? 'N') !== 'S') {
            return null;
        }

        $nfse = Nfse::query()
            ->where('reserva_id', $reserva->id)
            ->orderByDesc('id')
            ->first();

        if (!$nfse || !$nfse->nfse_id || $nfse->status === 'cancelada') {
            return $nfse;
        }

        $provider = $this->resolveProvider($empresa);
        $result = $provider->cancelar($empresa, $nfse, $motivo);
        $data = $result['response'] ?? null;

        if (($result['status'] ?? 'error') !== 'success') {
            $nfse->status = 'erro';
            $nfse->cancelamento_response = $data ?: ['message' => 'Falha ao cancelar NFS-e.'];
            $nfse->save();
            return $nfse;
        }

        $nfse->status = 'cancelada';
        $nfse->cancelamento_response = $data;
        $nfse->cancelada_em = now();
        $nfse->save();

        return $nfse;
    }

    public function baixarXml(Nfse $nfse, string $tipo = 'xml'): array
    {
        $empresa = $nfse->empresa ?: $this->resolveEmpresa($nfse->reserva ?? new Reserva());
        if (!$empresa) {
            return ['status' => 'error', 'message' => 'Empresa nao encontrada para a nota fiscal.'];
        }

        if (empty($nfse->nfse_id)) {
            return ['status' => 'error', 'message' => 'NFS-e ainda nao possui identificador para download.'];
        }

        $provider = $this->resolveProvider($empresa);
        if (!method_exists($provider, 'baixarXml')) {
            return ['status' => 'error', 'message' => 'Download indisponivel para o provedor atual.'];
        }

        /** @var array $result */
        $result = $provider->baixarXml($empresa, (string) $nfse->nfse_id, $tipo);
        if (($result['status'] ?? 'error') !== 'success') {
            return [
                'status' => 'error',
                'message' => $result['message'] ?? 'Falha ao baixar XML da NFS-e.',
            ];
        }

        return $result;
    }

    /**
     * @return array{total:int,atualizadas:int,ignoradas:int,erros:int}
     */
    public function sincronizarPendentes(?int $empresaId = null, ?int $limit = null): array
    {
        $query = Nfse::query()
            ->orderBy('id')
            ->where(function ($q): void {
                $q->whereNull('status')
                    ->orWhereIn('status', ['pendente', 'processando', 'erro']);
            })
            ->where(function ($q): void {
                $q->whereNull('nfse_id')
                    ->orWhereIn('status', ['pendente', 'processando', 'erro']);
            });

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $items = $query->get();
        $result = [
            'total' => $items->count(),
            'atualizadas' => 0,
            'ignoradas' => 0,
            'erros' => 0,
        ];

        $minimoAtualizacao = now()->subHour();

        foreach ($items as $nfse) {
            if ($nfse->ultima_consulta_em && $nfse->ultima_consulta_em->greaterThan($minimoAtualizacao)) {
                $result['ignoradas']++;
                continue;
            }

            $empresa = $nfse->empresa ?: ($nfse->empresa_id ? Empresa::query()->find($nfse->empresa_id) : null);
            if (!$empresa) {
                $result['erros']++;
                continue;
            }

            $nfseId = $nfse->nfse_id ?: null;
            if (!$nfseId && $nfse->dps_id) {
                $nfseId = 'dps:' . $nfse->dps_id;
            }

            if (!$nfseId) {
                $nfse->status = 'erro';
                $nfse->response = ['message' => 'NFS-e sem identificador para consulta.'];
                $nfse->ultima_consulta_em = now();
                $nfse->save();
                $result['erros']++;
                continue;
            }

            $provider = $this->resolveProvider($empresa);
            $consulta = $provider->consultar($empresa, (string) $nfseId);
            $data = $consulta['response'] ?? null;

            if (($consulta['status'] ?? 'error') !== 'success') {
                $nfse->status = 'erro';
                $nfse->response = $data ?: ['message' => 'Falha ao consultar NFS-e.'];
                $nfse->ultima_consulta_em = now();
                $nfse->save();
                $result['erros']++;
                continue;
            }

            $normalized = $this->normalizarStatus(is_array($data) ? $data : []);
            if ($normalized) {
                $nfse->status = $normalized;
            }

            if (!$nfse->nfse_id) {
                $nfse->nfse_id = $data['id'] ?? $data['nfse_id'] ?? ($data['nfse']['id'] ?? null) ?? $nfse->nfse_id;
            }

            if (!$nfse->dps_id) {
                $nfse->dps_id = $data['dps_id'] ?? ($data['dps']['id'] ?? null) ?? $nfse->dps_id;
            }

            $nfse->numero = $data['numero'] ?? ($data['nfse']['numero'] ?? null) ?? $nfse->numero;
            $nfse->serie = $data['serie'] ?? ($data['nfse']['serie'] ?? null) ?? $nfse->serie;
            $nfse->response = $data;
            $nfse->ultima_consulta_em = now();

            if ($nfse->status === 'cancelada' && !$nfse->cancelada_em) {
                $nfse->cancelada_em = now();
            }

            $nfse->save();
            $result['atualizadas']++;
        }

        return $result;
    }

    private function resolveEmpresa(Reserva $reserva): ?Empresa
    {
        if (isset($reserva->empresa_id)) {
            return Empresa::query()->find($reserva->empresa_id);
        }

        if (Schema::hasColumn('reservas', 'empresa_id') && !empty($reserva->empresa_id)) {
            return Empresa::query()->find($reserva->empresa_id);
        }

        return null;
    }

    private function buildPayload(Empresa $empresa, Reserva $reserva): array
    {
        $errors = [];
        $user = $reserva->usuario;

        $cnpj = $this->onlyDigits($empresa->cnpj ?? '');
        if ($cnpj === '') {
            $errors[] = 'CNPJ do prestador nao informado.';
        }

        // Municipio/IM podem variar por estação (empresa pode operar em mais de um município).
        $estacaoRetirada = null;
        if ($reserva->relationLoaded('estacaoRetirada')) {
            $estacaoRetirada = $reserva->estacaoRetirada;
        } elseif (!empty($reserva->estacao_retirada_id)) {
            $estacaoRetirada = Estacao::query()->find((int) $reserva->estacao_retirada_id);
        }

        $municipio = $this->onlyDigits($estacaoRetirada?->nfse_municipio_codigo ?: ($empresa->nfse_municipio_codigo ?? ''));
        if ($municipio === '') {
            $errors[] = 'Codigo do municipio nao informado.';
        }

        $codigoServico = trim((string) ($empresa->nfse_codigo_servico ?? ''));
        if ($codigoServico === '') {
            $errors[] = 'Codigo do servico nao informado.';
        }

        $descricaoServico = trim((string) ($empresa->nfse_descricao_servico ?? ''));
        if ($descricaoServico === '') {
            $errors[] = 'Descricao do servico nao informada.';
        }

        $aliquota = $empresa->nfse_aliquota;
        $tributacaoIssqn = $empresa->nfse_tributacao_issqn;
        $inscricaoMunicipal = $estacaoRetirada?->nfse_inscricao_municipal ?: ($empresa->nfse_inscricao_municipal ?: null);
        $cnae = $empresa->nfse_cnae ?: null;

        // Se houver configuração por município (tributos municipais), ela prevalece.
        if ($municipio !== '' && Schema::hasTable('tributos_municipais')) {
            $query = DB::table('tributos_municipais')
                ->where('empresa_id', (int) $empresa->id)
                ->where('municipio_codigo', $municipio)
                ->where('ativo', 'S');

            if ($codigoServico !== '') {
                $query->where('codigo_servico', $codigoServico);
            }

            $tributo = $query->orderByDesc('id')->first();

            if (!$tributo && $codigoServico !== '') {
                $tributo = DB::table('tributos_municipais')
                    ->where('empresa_id', (int) $empresa->id)
                    ->where('municipio_codigo', $municipio)
                    ->where('ativo', 'S')
                    ->orderByDesc('id')
                    ->first();
            }

            if ($tributo) {
                $codigoServico = trim((string) ($tributo->codigo_servico ?? $codigoServico));

                $descricaoFromTributo = trim((string) ($tributo->descricao_servico ?? ''));
                if ($descricaoFromTributo !== '') {
                    $descricaoServico = $descricaoFromTributo;
                }

                $aliquota = $tributo->aliquota_iss ?? $aliquota;
                $tributacaoIssqn = $tributo->tributacao_issqn ?? $tributacaoIssqn;

                // IM é do estabelecimento (estação) e não deve ser sobrescrita por tributo municipal.
                $cnae = $tributo->cnae ?: $cnae;
            }
        }

        if (isset($reserva->veiculo)) {
            $descricaoServico .= "\n\nInformacoes Obrigatorias (NT 005/2025):";
            $descricaoServico .= "\nVeiculo: " . $reserva->veiculo->marca . " " . $reserva->veiculo->modelo;
            $descricaoServico .= "\nPlaca: " . $reserva->veiculo->placa;
            if ($reserva->veiculo->renavam) {
                $descricaoServico .= "\nRENAVAM: " . $reserva->veiculo->renavam;
            }
            if ($reserva->data_inicio && $reserva->data_fim) {
                $descricaoServico .= "\nPeriodo de Locacao: " . Carbon::parse($reserva->data_inicio)->format('d/m/Y') . " a " . Carbon::parse($reserva->data_fim)->format('d/m/Y');
            }
        }

        if ($aliquota === null || $aliquota === '') {
            $errors[] = 'Aliquota do ISS nao informada.';
        }

        $doc = $this->onlyDigits($user?->numero_identificacao ?? '');
        $cpf = null;
        $cnpjTomador = null;
        if (strlen($doc) === 11) {
            $cpf = $doc;
        } elseif (strlen($doc) === 14) {
            $cnpjTomador = $doc;
        } else {
            $errors[] = 'Documento do tomador (CPF/CNPJ) nao informado ou invalido.';
        }

        $email = trim((string) ($user?->email ?? ''));
        $nome = trim((string) ($user?->nome ?? ''));
        if ($email === '' || $nome === '') {
            $errors[] = 'Nome ou e-mail do tomador nao informado.';
        }

        $endereco = trim((string) ($user?->endereco ?? ''));
        $cep = $this->onlyDigits((string) ($user?->zip_code ?? ''));
        $numero = trim((string) ($empresa->nfse_tomador_numero_padrao ?? ''));
        if ($numero === '') {
            $numero = $this->extractNumero($endereco);
        }
        $bairro = trim((string) ($empresa->nfse_tomador_bairro_padrao ?? ''));

        if ($endereco === '' || $cep === '') {
            $errors[] = 'Endereco ou CEP do tomador nao informado.';
        }

        $payload = [
            'ambiente' => $this->ambiente($empresa),
            'provedor' => $empresa->nfse_provedor ?: 'padrao',
            'referencia' => $this->referenciaReserva($reserva),
            'infDPS' => [
                'dhEmi' => Carbon::now()->toIso8601String(),
                'dCompet' => Carbon::now()->toDateString(),
                'prest' => array_filter([
                    'CNPJ' => $cnpj,
                    'IM' => $inscricaoMunicipal,
                ]),
                'toma' => array_filter([
                    'orgaoPublico' => false,
                    'CPF' => $cpf,
                    'CNPJ' => $cnpjTomador,
                    'xNome' => $nome,
                    'email' => $email,
                    'fone' => $this->onlyDigits((string) ($user?->telefone ?: $user?->celular ?? '')),
                    'end' => array_filter([
                        'endNac' => array_filter([
                            'cMun' => $municipio ?: null,
                            'CEP' => $cep ?: null,
                        ]),
                        'xLgr' => $endereco ?: null,
                        'nro' => $numero !== '' ? $numero : 'S/N',
                        'xBairro' => $bairro !== '' ? $bairro : null,
                    ]),
                ]),
                'serv' => [
                    'cLocPrestacao' => $municipio,
                    'cServ' => array_filter([
                        'cTribNac' => $codigoServico,
                        'CNAE' => $cnae,
                        'xDescServ' => $descricaoServico,
                    ]),
                    'valores' => [
                        'vServPrest' => [
                            'vServ' => (float) ($reserva->valor_total_final ?? 0),
                        ],
                        'trib' => [
                            'tribMun' => [
                                'tribISSQN' => (int) ($tributacaoIssqn ?: 1),
                                'cLocIncid' => $municipio,
                                'pAliq' => (float) $aliquota,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return [
            'payload' => $payload,
            'errors' => $errors,
        ];
    }

    private function resolveProvider(Empresa $empresa): NfseProviderInterface
    {
        $provedor = strtolower(trim((string) ($empresa->nfse_provedor ?? 'nuvemfiscal')));

        return match ($provedor) {
            'nfephp' => new NfephpProvider(),
            default => new NuvemFiscalProvider(),
        };
    }

    private function ambiente(Empresa $empresa): string
    {
        $ambiente = strtolower(trim((string) ($empresa->nfse_ambiente ?? 'homologacao')));
        return $ambiente === 'producao' ? 'producao' : 'homologacao';
    }

    private function referenciaReserva(Reserva $reserva, bool $reabertura = false): string
    {
        if ($reabertura) {
            return 'reserva-' . $reserva->id . '-reabertura-' . now()->format('YmdHis');
        }

        return 'reserva-' . $reserva->id;
    }

    private function onlyDigits(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/\D+/', '', $value);
    }

    private function extractNumero(string $endereco): string
    {
        if ($endereco === '') {
            return '';
        }

        if (preg_match('/\b(\d{1,6})\b/', $endereco, $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function normalizarStatus(array $data): ?string
    {
        $candidatos = [
            $data['status'] ?? null,
            $data['situacao'] ?? null,
            $data['estado'] ?? null,
            $data['nfse']['status'] ?? null,
            $data['nfse']['situacao'] ?? null,
            $data['dps']['status'] ?? null,
        ];

        foreach ($candidatos as $raw) {
            if ($raw === null) {
                continue;
            }

            $status = strtolower(trim((string) $raw));
            if ($status === '') {
                continue;
            }

            if (str_contains($status, 'cancel')) {
                return 'cancelada';
            }

            if (str_contains($status, 'pend') || str_contains($status, 'process')) {
                return 'processando';
            }

            if (
                in_array($status, ['emitida', 'autorizada', 'autorizado', 'concluida', 'concluido', 'success', 'sucesso', 'approved'], true)
                || str_contains($status, 'autoriz')
                || str_contains($status, 'emit')
            ) {
                return 'emitida';
            }

            if (
                str_contains($status, 'erro')
                || str_contains($status, 'reje')
                || str_contains($status, 'deneg')
            ) {
                return 'erro';
            }
        }

        return null;
    }
}

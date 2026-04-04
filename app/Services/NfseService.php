<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Nfse;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Services\Nfse\Providers\NfephpProvider;
use App\Services\Nfse\Providers\NfseProviderInterface;
use App\Services\Nfse\Providers\NuvemFiscalProvider;

class NfseService
{
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

        $nfse->status = 'emitida';
        $nfse->nfse_id = $data['id'] ?? $data['nfse_id'] ?? null;
        $nfse->dps_id = $data['dps_id'] ?? null;
        $nfse->numero = $data['numero'] ?? null;
        $nfse->serie = $data['serie'] ?? null;
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

        $municipio = $this->onlyDigits($empresa->nfse_municipio_codigo ?? '');
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
                    'IM' => $empresa->nfse_inscricao_municipal ?: null,
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
                        'CNAE' => $empresa->nfse_cnae ?: null,
                        'xDescServ' => $descricaoServico,
                    ]),
                    'valores' => [
                        'vServPrest' => [
                            'vServ' => (float) ($reserva->valor_total_final ?? 0),
                        ],
                        'trib' => [
                            'tribMun' => [
                                'tribISSQN' => (int) ($empresa->nfse_tributacao_issqn ?: 1),
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
}

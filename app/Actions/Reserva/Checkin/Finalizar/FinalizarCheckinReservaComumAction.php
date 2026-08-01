<?php

namespace App\Actions\Reserva\Checkin\Finalizar;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\DTO\Reserva\ReservaResolvida;
use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use App\Models\ContratoCheckin;
use App\Models\ReservaMotoristaApp;
use App\Models\TermoConsentimento;
use App\Support\ArquivoAnexoHelper;
use App\Services\ModeloService;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use App\Services\UpgradeVeiculoService;
use App\Services\VeiculoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class FinalizarCheckinReservaComumAction
{
    use ReservaHelpersTrait;

    public function __construct(
        private ReservaService $reservaService,
        private MovimentacaoService $movimentacaoService,
        private ExibirContratoCheckinReservaComumAction $exibirContratoAction,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService,
    ) {}

    public function handle(
        ReservaResolvida $reserva,
        Request $request
    ): mixed {
        $registro = $reserva->registro;

        $registro->loadMissing([
            'contratoCheckin',
            'usuario',
        ]);

        $contratoId = (int) $request->input(
            'contrato_id',
            $registro->contratoCheckin?->id
        );

        $veiculoId = (int) $request->input(
            'veiculo_id',
            $registro->contratoCheckin?->veiculo_id
        );

        $assinatura = (string) $request->input('assinatura');
        $modo = (string) $request->input('modo', 'digital');

        $contrato = ContratoCheckin::query()
            ->whereKey($contratoId)
            ->where('reserva_id', $registro->id)
            ->first();

        if (! $contrato) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato de check-in não encontrado.',
            ], 404);
        }

        if ($contrato->assinado === 'S') {
            if (
                blank($contrato->pdf_path)
                || ! Storage::disk('public')->exists($contrato->pdf_path)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'O contrato está marcado como assinado, mas o PDF salvo não foi encontrado.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Este contrato já estava assinado.',
                'redirect' => $this->obterRedirectUrl($reserva, $request),
                'pdf_path' => $contrato->pdf_path,
                'pdf_url' => Storage::disk('public')->url(
                    $contrato->pdf_path
                ),
                'pdf_hash' => $contrato->pdf_hash,
                'assinado' => true,
            ]);
        }

        $arquivoPdfSalvo = null;

        try {
            DB::beginTransaction();

            $this->preencherDadosAssinatura(
                contrato: $contrato,
                registro: $registro,
                request: $request,
            );

            if ($modo === 'upload') {
                $resultadoPdf = $this->salvarPdfEnviado(
                    registro: $registro,
                    contrato: $contrato,
                    request: $request,
                );
            } else {
                if (blank($assinatura)) {
                    throw new RuntimeException(
                        'A assinatura digital não foi informada.'
                    );
                }

                $resultadoPdf = $this->gerarESalvarPdfContrato(
                    registro: $registro,
                    contrato: $contrato,
                    assinatura: $assinatura,
                );
            }

            $arquivoPdfSalvo = $resultadoPdf['pdf_path'];

            $contrato->forceFill([
                'assinatura' => null,
                'pdf_path' => $resultadoPdf['pdf_path'],
                'pdf_hash' => $resultadoPdf['pdf_hash'],
                'assinado' => 'S',
                'data_assinatura' => $contrato->data_assinatura ?? now(),
            ])->save();

            $contrato->refresh();

            $this->reservaService->checkInRealizado(
                (int) $registro->id,
                (int) $contrato->veiculo_id
            );

            $dataChegadaPrevista = $registro->data_fim
                ? Carbon::parse($registro->data_fim)->toDateTimeString()
                : null;

            $this->movimentacaoService->realizarCheckIn(
                $veiculoId,
                (int) $registro->estacao_retirada_id,
                (int) $registro->id,
                'Check-in realizado e contrato assinado',
                $dataChegadaPrevista,
                (int) (
                    $request->input('quilometragem_atual')
                    ?? $contrato->quilometragem_atual
                    ?? 0
                )
            );

            $this->reservaService->adicionaLog(
                (int) $registro->id,
                Auth::id(),
                [
                    'veiculo' => $contrato->veiculo?->nome
                        ?? "ID {$contrato->veiculo_id}",
                ],
                'Contrato de check-in assinado'
                    .($modo === 'upload' ? ' (PDF anexado)' : ''),
                'success'
            );

            $this->sincronizarAssinaturaCheckinMotoristaApp(
                registro: $registro,
                contratoId: $contrato->id,
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-in finalizado com sucesso.',
                'redirect' => $this->obterRedirectUrl($reserva, $request),
                'pdf_path' => $contrato->pdf_path,
                'pdf_url' => Storage::disk('public')->url(
                    $contrato->pdf_path
                ),
                'pdf_hash' => $contrato->pdf_hash,
                'assinado' => true,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            if (
                filled($arquivoPdfSalvo)
                && Storage::disk('public')->exists($arquivoPdfSalvo)
            ) {
                Storage::disk('public')->delete($arquivoPdfSalvo);
            }

            Log::error('Erro ao finalizar check-in da reserva', [
                'reserva_id' => $registro->id,
                'contrato_id' => $contrato->id,
                'modo' => $modo,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar check-in: '.$e->getMessage(),
            ], 500);
        }
    }

    private function preencherDadosAssinatura(
        ContratoCheckin $contrato,
        $registro,
        Request $request
    ): void {
        $usuario = $registro->usuario
            ?? $registro->user
            ?? Auth::user();

        $aceitouTermo = $request->boolean(
            'aceite_termo_checkin'
        ) ? 'S' : 'N';

        $termoTexto = $contrato->termo_texto;

        if ($aceitouTermo === 'S') {
            $termos = TermoConsentimento::query()
                ->where('empresa_id', $registro->empresa_id)
                ->whereIn('tipo', [
                    'checkin-reserva',
                    'aceito-reserva',
                ])
                ->where('ativo', 'S')
                ->orderBy('ordem')
                ->get(['titulo', 'texto']);

            if ($termos->isNotEmpty()) {
                $termoTexto = $termos
                    ->map(function ($termo) {
                        $titulo = filled($termo->titulo)
                            ? '<h3>'.$termo->titulo.'</h3>'
                            : '';

                        return $titulo.$termo->texto;
                    })
                    ->implode('');
            }
        }

        $contrato->forceFill([
            'assinatura' => null,
            'assinatura_ip' => $request->ip(),
            'assinatura_dispositivo' => substr(
                (string) $request->userAgent(),
                0,
                255
            ),
            'signatario_nome' => $usuario?->nome
                ?? $usuario?->name
                ?? Auth::user()?->nome
                ?? Auth::user()?->name,
            'signatario_cpf' => $usuario?->cpf
                ?? $usuario?->documento
                ?? null,
            'aceite_termo' => $aceitouTermo,
            'termo_texto' => $termoTexto,
            'data_assinatura' => now(),
            'assinado' => 'N',
            'pdf_path' => null,
            'pdf_hash' => null,
        ])->save();
    }

    private function salvarPdfEnviado(
        $registro,
        ContratoCheckin $contrato,
        Request $request
    ): array {
        if (! $request->hasFile('contrato_pdf')) {
            throw new RuntimeException(
                'O arquivo PDF do contrato não foi enviado.'
            );
        }

        $arquivo = $request->file('contrato_pdf');

        if (! $arquivo->isValid()) {
            throw new RuntimeException(
                'O arquivo enviado é inválido.'
            );
        }

        if (
            $arquivo->getMimeType() !== 'application/pdf'
            && strtolower($arquivo->getClientOriginalExtension()) !== 'pdf'
        ) {
            throw new RuntimeException(
                'Arquivo inválido. Envie um documento PDF.'
            );
        }

        $diretorio = $this->obterDiretorioPdf($registro);

        $nomeArquivo = sprintf(
            'contrato-checkin-%s-upload-%s.pdf',
            $registro->id,
            now()->format('YmdHis')
        );

        $pdfPath = ArquivoAnexoHelper::salvarConteudoRastreado(
            file_get_contents($arquivo->getRealPath() ?: $arquivo->getPathname()),
            $diretorio,
            [
                'empresa_id' => $registro->empresa_id ?? null,
                'user_id' => Auth::id(),
                'modulo' => 'checkin',
                'entidade' => 'contrato_checkin',
                'entidade_id' => $contrato->id ?? null,
                'campo' => 'pdf_path',
                'contexto' => 'checkin-pdf-upload',
                'mime_type' => 'application/pdf',
            ],
            $nomeArquivo
        );

        if (blank($pdfPath)) {
            throw new RuntimeException(
                'Não foi possível salvar o PDF enviado.'
            );
        }

        if (! Storage::disk('public')->exists($pdfPath)) {
            throw new RuntimeException(
                'O PDF enviado não foi encontrado após o salvamento.'
            );
        }

        $pdfContent = Storage::disk('public')->get($pdfPath);

        if (! str_starts_with($pdfContent, '%PDF')) {
            Storage::disk('public')->delete($pdfPath);

            throw new RuntimeException(
                'O arquivo enviado não possui um conteúdo PDF válido.'
            );
        }

        return [
            'pdf_path' => $pdfPath,
            'pdf_hash' => hash('sha256', $pdfContent),
        ];
    }

    private function gerarESalvarPdfContrato(
        $registro,
        ContratoCheckin $contrato,
        string $assinatura
    ): array {
        $registro->loadMissing([
            'usuario',
            'empresa',
            'veiculo',
            'grupoFrota',
            'estacaoRetirada',
            'estacaoDevolucao',
            'opcionais',
            'contratoCheckin',
            'modelos',
            'statusReserva',
            'cupomDesconto',
            'transacoes.user',
            'transacoes.pagoPor',
            'contratoCheckin.marca',
            'contratoCheckin.modelo',
            'contratoCheckin.veiculo.marca',
            'contratoCheckin.veiculo.modelo',
            'contratoCheckin.veiculo.grupoFrota',
            'contratoCheckin.grupoFrotaOriginal',
            'contratoCheckin.veiculoPrevisto',
            'contratoCheckin.usuarioUpgrade',
            'contratoCheckin.calcao',
            'contratoCheckout',
            'calcao',
            'calcao.configuracao',
            'veiculoDanos.dano.categoria',
            'veiculoDanos.imagem',
            'veiculoDanos.usuarioCorrecao',
            'avariaCobrancas',
            'multas',
            'nfseAtual',
        ]);

        $contrato->refresh();

        /*
         * A assinatura fica somente em memória para compor o PDF.
         * Não execute save() depois de definir esse atributo.
         */
        $contrato->setAttribute('assinatura', $assinatura);
        $contrato->setAttribute('assinado', 'N');

        $registro->setRelation('contratoCheckin', $contrato);

        if (blank($registro->contratoCheckin?->assinatura)) {
            throw new RuntimeException(
                'A assinatura não está disponível para montar o PDF.'
            );
        }

        $pdfContent = $this->exibirContratoAction->gerarPdf(
            $registro
        );

        if (blank($pdfContent)) {
            throw new RuntimeException(
                'O conteúdo do PDF não foi gerado.'
            );
        }

        if (! str_starts_with($pdfContent, '%PDF')) {
            throw new RuntimeException(
                'O conteúdo gerado não corresponde a um PDF válido.'
            );
        }

        $diretorio = $this->obterDiretorioPdf($registro);

        $nomeArquivo = sprintf(
            'contrato-checkin-%s-%s.pdf',
            $registro->id,
            now()->format('YmdHis')
        );

        $pdfPath = $diretorio.'/'.$nomeArquivo;

        $pdfPath = ArquivoAnexoHelper::salvarConteudoRastreado(
            $pdfContent,
            $diretorio,
            [
                'empresa_id' => $registro->empresa_id ?? null,
                'user_id' => Auth::id(),
                'modulo' => 'checkin',
                'entidade' => 'contrato_checkin',
                'entidade_id' => $contrato->id,
                'campo' => 'pdf_path',
                'contexto' => 'checkin-pdf',
                'mime_type' => 'application/pdf',
            ],
            $nomeArquivo
        );

        if (blank($pdfPath) || ! Storage::disk('public')->exists($pdfPath)) {
            throw new RuntimeException(
                'Não foi possível salvar o PDF definitivo do contrato.'
            );
        }

        return [
            'pdf_path' => $pdfPath,
            'pdf_hash' => hash('sha256', $pdfContent),
        ];
    }

    private function obterDiretorioPdf($registro): string
    {
        return sprintf(
            'contratos/checkin/empresa_%s/reserva_%s',
            $registro->empresa_id,
            $registro->id
        );
    }

    private function obterRedirectUrl(
        ReservaResolvida $reserva,
        Request $request
    ): string {
        if ($request->filled('retorno')) {
            return route(
                'reserva.show',
                $request->input('retorno')
            );
        }

        if (
            $request->boolean('origem_minhas')
            || (Auth::user() && ! Auth::user()->isAdministrador())
        ) {
            return route('reserva.minhas.show', [
                'id' => $reserva->codigo,
            ]);
        }

        return route(
            'reserva.show',
            $reserva->codigo
        );
    }

    private function sincronizarAssinaturaCheckinMotoristaApp(
        $registro,
        int $contratoId
    ): void {
        $motoristaApp = ReservaMotoristaApp::query()
            ->whereJsonContains(
                'documentos_json->checkin->reserva_operacional_id',
                (int) $registro->id
            )
            ->first();

        if (
            ! $motoristaApp
            && filled($registro->observacoes)
            && preg_match(
                '/(MAP-\d+-\d+)/',
                (string) $registro->observacoes,
                $matches
            )
        ) {
            $codigoMap = $matches[1];

            $motoristaApp = ReservaMotoristaApp::query()
                ->whereHas(
                    'codigoReserva',
                    fn ($query) => $query->where(
                        'codigo',
                        $codigoMap
                    )
                )
                ->first();
        }

        if (! $motoristaApp) {
            return;
        }

        $contrato = ContratoCheckin::find($contratoId);

        if (! $contrato) {
            return;
        }

        $documentos = is_array(
            $motoristaApp->documentos_json
        )
            ? $motoristaApp->documentos_json
            : [];

        data_set(
            $documentos,
            'checkin.reserva_operacional_id',
            (int) $registro->id
        );

        data_set(
            $documentos,
            'checkin.contrato_checkin_id',
            $contratoId
        );

        data_set(
            $documentos,
            'checkin.assinado',
            $contrato->assinado === 'S'
        );

        data_set(
            $documentos,
            'checkin.assinado_em',
            $contrato->data_assinatura
                ? Carbon::parse(
                    $contrato->data_assinatura
                )->toDateTimeString()
                : now()->toDateTimeString()
        );

        data_set(
            $documentos,
            'checkin.pdf_path',
            $contrato->pdf_path
        );

        data_set(
            $documentos,
            'checkin.pdf_url',
            $contrato->pdf_path
                ? Storage::disk('public')->url(
                    $contrato->pdf_path
                )
                : null
        );

        $snapshot = is_array(
            $motoristaApp->contrato_snapshot_json
        )
            ? $motoristaApp->contrato_snapshot_json
            : [];

        $dataAssinatura = Carbon::parse(
            $contrato->data_assinatura ?? now()
        )->format('d/m/Y H:i');

        data_forget($snapshot, 'assinatura');

        data_set(
            $snapshot,
            'documento.data_assinatura',
            $dataAssinatura
        );

        data_set(
            $snapshot,
            'contrato.assinado_em',
            $dataAssinatura
        );

        if ($contrato->pdf_path) {
            data_set(
                $snapshot,
                'contrato.pdf_path',
                $contrato->pdf_path
            );

            data_set(
                $snapshot,
                'contrato.pdf_url',
                Storage::disk('public')->url(
                    $contrato->pdf_path
                )
            );
        }

        $motoristaApp->forceFill([
            'documentos_json' => $documentos,
            'contrato_snapshot_json' => $snapshot,
            'contrato_pdf_path' => $contrato->pdf_path
                ?: $motoristaApp->contrato_pdf_path,
            'status' => 'ativa',
        ])->save();
    }
}

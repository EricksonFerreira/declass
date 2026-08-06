<?php

namespace App\Actions\Reserva\Checkin\FinalizarMinhaReserva;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\DTO\Reserva\ReservaResolvida;
use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use App\Models\ContratoCheckin;
use App\Models\ReservaMotoristaApp;
use App\Models\TermoConsentimento;
use App\Services\ModeloService;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use App\Services\UpgradeVeiculoService;
use App\Services\VeiculoService;
use App\Support\SchemaCache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class FinalizarMinhaReservaCheckinReservaComumAction
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

        $modo = (string) $request->input('modo', 'digital');
        $assinatura = (string) $request->input('assinatura');

        /*
         * Além de localizar o contrato, garante que ele realmente
         * pertence à reserva que está sendo finalizada.
         */
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

        /*
         * Se o contrato já estiver assinado e o PDF existir,
         * não gera nem salva novamente.
         */
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

        /*
         * O Storage não participa da transação do banco.
         * Esse valor será usado para remover o PDF caso alguma
         * operação posterior falhe.
         */
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
                    request: $request,
                );
            } else {
                if (blank($assinatura)) {
                    throw new RuntimeException(
                        'A assinatura digital não foi informada.'
                    );
                }

                /*
                 * A assinatura não é salva no banco.
                 * Ela será adicionada somente à instância em memória
                 * utilizada para montar o PDF.
                 */
                $resultadoPdf = $this->gerarESalvarPdfContrato(
                    registro: $registro,
                    contrato: $contrato,
                    assinatura: $assinatura,
                );
            }

            $arquivoPdfSalvo = $resultadoPdf['pdf_path'];

            /*
             * O contrato somente é marcado como assinado após o PDF
             * ter sido gerado, salvo e encontrado no Storage.
             *
             * A assinatura permanece nula no banco.
             */
            $contrato->forceFill([
                'assinatura' => null,
                'pdf_path' => $resultadoPdf['pdf_path'],
                'pdf_hash' => $resultadoPdf['pdf_hash'],
                'assinado' => 'S',
                'data_assinatura' => $contrato->data_assinatura ?? now(),
            ])->save();

            $contrato->refresh();

            if (SchemaCache::hasColumn($registro->getTable(), 'checkin_assinado_em')) {
                $registro->forceFill([
                    'checkin_assinado_em' => $contrato->data_assinatura ?? now(),
                ])->save();
            }

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

            /*
             * Remove o PDF caso ele tenha sido salvo, mas alguma
             * operação posterior tenha causado rollback no banco.
             */
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
                /*
                 * Preserva todos os termos ativos no texto que
                 * comprova o aceite do cliente.
                 */
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
            /*
             * A imagem da assinatura não será salva no banco.
             */
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

            /*
             * O contrato ainda não está concluído, pois o PDF
             * definitivo ainda não foi salvo.
             */
            'assinado' => 'N',
            'pdf_path' => null,
            'pdf_hash' => null,
        ])->save();
    }

    private function salvarPdfEnviado(
        $registro,
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

        $pdfPath = $arquivo->storeAs(
            $diretorio,
            $nomeArquivo,
            'public'
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
    /*
     * Usa a própria reserva já resolvida no início do fluxo.
     * Evita uma nova consulta que pode retornar null por causa
     * de scopes ou filtros existentes no ReservaService.
     */
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
     * A assinatura fica somente na instância em memória.
     * Nenhum save será executado com a base64.
     */
    $contrato->setAttribute('assinatura', $assinatura);
    $contrato->setAttribute('assinado', 'N');

    /*
     * Força a reserva a utilizar o contrato que contém
     * temporariamente a assinatura.
     */
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

    $salvo = Storage::disk('public')->put(
        $pdfPath,
        $pdfContent
    );

    if (! $salvo) {
        throw new RuntimeException(
            'Não foi possível salvar o PDF definitivo do contrato.'
        );
    }

    if (! Storage::disk('public')->exists($pdfPath)) {
        throw new RuntimeException(
            'O PDF não foi encontrado após o salvamento.'
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

        /*
         * Não adiciona a imagem da assinatura ao snapshot.
         * O documento definitivo é o PDF salvo.
         */
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

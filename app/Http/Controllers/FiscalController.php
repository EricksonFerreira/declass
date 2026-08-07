<?php

namespace App\Http\Controllers;

use App\Models\OperacaoFiscal;
use App\Models\RegraTributaria;
use App\Models\CreditoTributario;
use App\Models\ClassificacaoFiscal;
use App\DTO\BreadCrumbItem;
use App\Services\BreadcrumbService;
use App\Modules\Fiscal\Actions\ImportarNfeXmlAction;
use Illuminate\Http\Request;
use Exception;

class FiscalController extends Controller
{
    public function __construct(
        protected BreadcrumbService $breadcrumbService
    ) {}

    public function dashboard()
    {
        $operacoesCount = OperacaoFiscal::count();
        $creditosDisponiveis = CreditoTributario::where('status', 'DISPONIVEL')->sum('saldo');
        $debitosIbs = OperacaoFiscal::join('operacoes_fiscais_tributos', 'operacoes_fiscais.id', '=', 'operacoes_fiscais_tributos.operacao_fiscal_id')
            ->where('operacoes_fiscais_tributos.tributo', 'IBS')
            ->sum('operacoes_fiscais_tributos.valor');
        
        $debitosCbs = OperacaoFiscal::join('operacoes_fiscais_tributos', 'operacoes_fiscais.id', '=', 'operacoes_fiscais_tributos.operacao_fiscal_id')
            ->where('operacoes_fiscais_tributos.tributo', 'CBS')
            ->sum('operacoes_fiscais_tributos.valor');

        $saldoEstimado = max(0, ($debitosIbs + $debitosCbs) - $creditosDisponiveis);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', '#'),
        ];

        return view('fiscal.dashboard', compact(
            'operacoesCount', 'creditosDisponiveis', 'debitosIbs', 'debitosCbs', 'saldoEstimado', 'breadcrumb'
        ));
    }

    public function operacoes()
    {
        $operacoes = OperacaoFiscal::with(['locacao', 'tributos'])->paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Operações Fiscais', '#'),
        ];

        return view('fiscal.operacoes', compact('operacoes', 'breadcrumb'));
    }

    // --- REGRAS TRIBUTÁRIAS ---
    public function regras()
    {
        $regras = RegraTributaria::paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Regras Tributárias', '#'),
        ];

        return view('fiscal.regras', compact('regras', 'breadcrumb'));
    }

    public function regrasCriar()
    {
        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Regras Tributárias', route('fiscal.regras')),
            new BreadCrumbItem('Nova Regra', '#'),
        ];

        return view('fiscal.regras_form', compact('breadcrumb'));
    }

    public function regrasSalvar(Request $request)
    {
        $validated = $request->validate([
            'tributo' => 'required|string|in:IBS,CBS,PIS,COFINS',
            'tipo_operacao' => 'required|string|max:50',
            'aliquota' => 'required|numeric|min:0|max:100',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'gera_credito' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ]);

        $validated['gera_credito'] = $request->has('gera_credito');
        $validated['ativo'] = $request->has('ativo');

        RegraTributaria::create($validated);

        return redirect()->route('fiscal.regras')->with('success', 'Regra tributária criada com sucesso!');
    }

    public function regrasEditar($id)
    {
        $regra = RegraTributaria::findOrFail($id);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Regras Tributárias', route('fiscal.regras')),
            new BreadCrumbItem('Editar Regra', '#'),
        ];

        return view('fiscal.regras_form', compact('regra', 'breadcrumb'));
    }

    public function regrasAtualizar(Request $request, $id)
    {
        $regra = RegraTributaria::findOrFail($id);

        $validated = $request->validate([
            'tributo' => 'required|string|in:IBS,CBS,PIS,COFINS',
            'tipo_operacao' => 'required|string|max:50',
            'aliquota' => 'required|numeric|min:0|max:100',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'gera_credito' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ]);

        $validated['gera_credito'] = $request->has('gera_credito');
        $validated['ativo'] = $request->has('ativo');

        $regra->update($validated);

        return redirect()->route('fiscal.regras')->with('success', 'Regra tributária atualizada com sucesso!');
    }

    public function regrasExcluir($id)
    {
        $regra = RegraTributaria::findOrFail($id);
        $regra->delete();

        return redirect()->route('fiscal.regras')->with('success', 'Regra tributária excluída com sucesso!');
    }

    // --- CRÉDITOS TRIBUTÁRIOS ---
    public function creditos()
    {
        $creditos = CreditoTributario::paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Créditos Tributários', '#'),
        ];

        return view('fiscal.creditos', compact('creditos', 'breadcrumb'));
    }

    public function creditosCriar()
    {
        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Créditos Tributários', route('fiscal.creditos')),
            new BreadCrumbItem('Novo Crédito', '#'),
        ];

        return view('fiscal.creditos_form', compact('breadcrumb'));
    }

    public function creditosSalvar(Request $request)
    {
        $validated = $request->validate([
            'tributo' => 'required|string|in:IBS,CBS',
            'valor_original' => 'required|numeric|min:0.01',
            'origem' => 'required|string|max:100',
            'data_apropriacao' => 'required|date',
        ]);

        $validated['empresa_id'] = app(\App\Support\EmpresaContext::class)->empresaId() ?: 1;
        $validated['valor_utilizado'] = 0.00;
        $validated['saldo'] = $validated['valor_original'];
        $validated['status'] = 'DISPONIVEL';

        CreditoTributario::create($validated);

        return redirect()->route('fiscal.creditos')->with('success', 'Crédito tributário registrado com sucesso!');
    }

    public function importarXml(Request $request, ImportarNfeXmlAction $action)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml',
        ]);

        try {
            $empresaId = app(\App\Support\EmpresaContext::class)->empresaId() ?: 1;
            $xmlContent = file_get_contents($request->file('xml_file')->getRealPath());
            
            $action->handle($xmlContent, $empresaId);

            return redirect()->route('fiscal.creditos')->with('success', 'XML importado com sucesso! Créditos de IBS/CBS gerados automaticamente.');
        } catch (Exception $e) {
            return redirect()->route('fiscal.creditos')->with('error', 'Falha na importação do XML: ' . $e->getMessage());
        }
    }

    // --- CLASSIFICAÇÕES FISCAIS (NCM) ---
    public function classificacoes()
    {
        $classificacoes = ClassificacaoFiscal::paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Classificações Fiscais', '#'),
        ];

        return view('fiscal.classificacoes', compact('classificacoes', 'breadcrumb'));
    }

    public function classificacoesCriar()
    {
        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Classificações Fiscais', route('fiscal.classificacoes')),
            new BreadCrumbItem('Nova Classificação', '#'),
        ];

        return view('fiscal.classificacoes_form', compact('breadcrumb'));
    }

    public function classificacoesSalvar(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'ncm' => 'required|string|max:8',
            'tipo_bem' => 'required|string|max:50',
        ]);

        ClassificacaoFiscal::create($validated);

        return redirect()->route('fiscal.classificacoes')->with('success', 'Classificação fiscal (NCM) criada com sucesso!');
    }

    // --- EXPORTAÇÃO CONTÁBIL ---
    public function exportarContabil(Request $request)
    {
        $empresaId = app(\App\Support\EmpresaContext::class)->empresaId() ?: 1;
        $mes = $request->input('mes', date('m'));
        $ano = $request->input('ano', date('Y'));

        $operacoes = OperacaoFiscal::with('tributos')
            ->where('empresa_id', $empresaId)
            ->whereMonth('data_fato_gerador', $mes)
            ->whereYear('data_fato_gerador', $ano)
            ->get();

        $creditos = CreditoTributario::where('empresa_id', $empresaId)
            ->whereMonth('data_apropriacao', $mes)
            ->whereYear('data_apropriacao', $ano)
            ->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=fechamento_fiscal_{$ano}_{$mes}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($operacoes, $creditos) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Section 1: Debitos (Operações)
            fputcsv($file, ['--- DEBITOS APURADOS (SAIDAS) ---']);
            fputcsv($file, ['ID Operacao', 'Tipo', 'Data Fato Gerador', 'Valor Operacao', 'Base Calculo', 'IBS Calculado', 'CBS Calculado', 'Status']);
            
            foreach ($operacoes as $op) {
                $ibsVal = $op->tributos->where('tributo', 'IBS')->sum('valor');
                $cbsVal = $op->tributos->where('tributo', 'CBS')->sum('valor');
                fputcsv($file, [
                    $op->id,
                    $op->tipo,
                    $op->data_fato_gerador->format('d/m/Y H:i'),
                    number_format($op->valor_operacao, 2, '.', ''),
                    number_format($op->valor_base_calculo, 2, '.', ''),
                    number_format($ibsVal, 2, '.', ''),
                    number_format($cbsVal, 2, '.', ''),
                    $op->status
                ]);
            }

            fputcsv($file, []); // empty line

            // Section 2: Creditos (Entradas)
            fputcsv($file, ['--- CREDITOS APROPRIADOS (ENTRADAS) ---']);
            fputcsv($file, ['ID Credito', 'Tributo', 'Valor Original', 'Valor Utilizado', 'Saldo Disponivel', 'Origem', 'Data Apropriacao', 'Status']);
            
            foreach ($creditos as $cr) {
                fputcsv($file, [
                    $cr->id,
                    $cr->tributo,
                    number_format($cr->valor_original, 2, '.', ''),
                    number_format($cr->valor_utilizado, 2, '.', ''),
                    number_format($cr->saldo, 2, '.', ''),
                    $cr->origem,
                    $cr->data_apropriacao->format('d/m/Y H:i'),
                    $cr->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function configuracoes()
    {
        $empresaId = app(\App\Support\EmpresaContext::class)->empresaId() ?: 1;
        $empresa = \App\Models\Empresa::findOrFail($empresaId);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Configurações Fiscais', '#'),
        ];

        return view('fiscal.configuracoes', compact('empresa', 'breadcrumb'));
    }

    public function configuracoesSalvar(Request $request)
    {
        $empresaId = app(\App\Support\EmpresaContext::class)->empresaId() ?: 1;
        $empresa = \App\Models\Empresa::findOrFail($empresaId);

        $validated = $request->validate([
            'regime_emissao' => 'required|string|in:NFSE_APENAS,RECIBO_APENAS,MISTO',
            'certificado_file' => 'nullable|file|max:5120',
            'certificado_senha' => 'nullable|string|max:100',
            'fiscal_token' => 'nullable|string|max:255',
            'fiscal_client_id' => 'nullable|string|max:255',
            'nfse_ativo' => 'nullable|boolean',
            'nfse_ambiente' => 'required|string|in:homologacao,producao',
            'nfse_inscricao_municipal' => 'nullable|string|max:30',
            'nfse_municipio_codigo' => 'nullable|string|max:10',
            'nfse_codigo_servico' => 'nullable|string|max:20',
            'nfse_cnae' => 'nullable|string|max:20',
            'nfse_aliquota' => 'nullable|numeric|min:0|max:100',
            'nfse_descricao_servico' => 'nullable|string',
        ]);

        $validated['nfse_ativo'] = $request->has('nfse_ativo');

        if ($request->hasFile('certificado_file')) {
            $file = $request->file('certificado_file');
            $path = $file->store('certificados', 'local');
            $validated['certificado_path'] = $path;

            // Track file inside arquivos_rastreados
            \App\Models\ArquivoRastreado::create([
                'empresa_id' => $empresaId,
                'user_id' => auth()->id(),
                'modulo' => 'fiscal',
                'entidade' => 'App\Models\Empresa',
                'entidade_id' => $empresaId,
                'campo' => 'certificado_path',
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'hash_sha1' => sha1_file($file->getRealPath()),
            ]);
        }

        if (empty($validated['certificado_senha'])) {
            unset($validated['certificado_senha']);
        }

        $empresa->update($validated);

        return redirect()->route('fiscal.config')->with('success', 'Configurações fiscais atualizadas com sucesso!');
    }
}

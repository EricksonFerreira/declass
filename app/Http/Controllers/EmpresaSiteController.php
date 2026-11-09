<?php

namespace App\Http\Controllers;

use App\Http\Requests\Empresa\UpdateEmpresaSiteRequest;
use App\Models\Empresa;
use App\Services\BreadcrumbService;
use App\Services\EmpresaService;
use App\Services\SiteEmpresaService;
use App\Support\UploadStorage;
use Illuminate\Support\Facades\Storage;

/**
 * Configuracao do site publico da empresa ("rede de escolas"):
 * liga/desliga a apresentacao, escolhe o modelo de layout e
 * edita textos e imagens exibidos na pagina.
 */
class EmpresaSiteController extends Controller
{
    public function __construct(
        private EmpresaService $empresaService,
        private BreadcrumbService $breadcrumbService,
        private SiteEmpresaService $siteService
    ) {
        $this->middleware('can:gerenciar-empresa');
    }

    public function edit(Empresa $empresa)
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresas', 'empresa.index', "Site público: {$empresa->nome}", 'Configurações');

        return view('empresa.site', [
            'empresa' => $empresa,
            'breadcrumb' => $breadcrumb,
            'modelos' => Empresa::SITE_MODELOS,
            'titulo' => 'Site público',
        ]);
    }

    /**
     * Pre-visualizacao de um modelo com os dados reais da empresa,
     * sem salvar e sem exigir site_ativo — usada pelo modal da tela
     * de configuracao ("ver como ficaria").
     */
    public function preview(\Illuminate\Http\Request $request, Empresa $empresa)
    {
        $modelo = (string) $request->input('modelo', $empresa->site_modelo ?: 'classico');
        abort_unless(array_key_exists($modelo, Empresa::SITE_MODELOS), 404);

        return view('site.modelos.'.$modelo, $this->siteService->dados($empresa, preview: true));
    }

    public function update(UpdateEmpresaSiteRequest $request, Empresa $empresa)
    {
        $data = $request->validated();

        foreach (['site_banner', 'site_imagem_sobre'] as $campo) {
            $arquivo = $data[$campo] ?? null;
            unset($data[$campo]);

            if (! $arquivo instanceof \Illuminate\Http\UploadedFile || ! $arquivo->isValid()) {
                continue;
            }

            if ($empresa->{$campo}) {
                Storage::disk('public')->delete($empresa->{$campo});
            }

            $data[$campo] = UploadStorage::putFile($arquivo, "empresas/{$empresa->id}/site", null, 'public');
        }

        $galeria = collect($empresa->site_galeria ?? []);
        $remover = collect($data['galeria_remover'] ?? [])->intersect($galeria->all());
        unset($data['site_galeria'], $data['galeria_remover']);

        $remover->each(fn ($path) => Storage::disk('public')->delete($path));
        $galeria = $galeria->reject(fn ($path) => $remover->contains($path));

        foreach ($request->file('site_galeria', []) as $arquivo) {
            if ($arquivo instanceof \Illuminate\Http\UploadedFile && $arquivo->isValid()) {
                $galeria->push(UploadStorage::putFile($arquivo, "empresas/{$empresa->id}/site/galeria", null, 'public'));
            }
        }

        $data['site_galeria'] = $galeria->filter()->values()->all();

        $this->empresaService->update($empresa->id, $data);

        return redirect()
            ->route('empresa.site.edit', $empresa)
            ->with('success', 'Site público atualizado com sucesso.');
    }
}

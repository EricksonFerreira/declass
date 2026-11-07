<?php

namespace App\Http\Controllers;

use App\Models\Empresa;

/**
 * Site publico da empresa ("rede de escolas"): pagina de exibicao
 * da marca, das unidades e dos contatos do tenant — sem login.
 * Servida em /rede/{slug} na plataforma e na raiz do dominio
 * personalizado da empresa.
 */
class SiteEmpresaController extends Controller
{
    /**
     * Pagina do site da empresa por slug (/rede/{slug}) ou pela
     * empresa ja resolvida pelo dominio personalizado (raiz do dominio).
     */
    public function show(?Empresa $empresa = null)
    {
        // Quando vem da rota /rede/{empresa:slug}, o binding ja injetou
        // o model; quando vem do dominio, o controller o recebe por aqui.
        $empresa ??= $this->empresaDoHost();

        abort_if(! $empresa?->sitePublicoAtivo(), 404);

        $escolas = $empresa->escolas()
            ->where('ativa', 'S')
            ->orderBy('nome')
            ->get(['id', 'empresa_id', 'nome', 'slug', 'email', 'telefone', 'endereco', 'cidade', 'estado', 'logo_path']);

        return view('site.empresa', [
            'empresa' => $empresa,
            'escolas' => $escolas,
        ]);
    }

    /**
     * Empresa ativa dona do host atual — usada pela raiz do dominio
     * personalizado, que chama o controller sem slug.
     */
    private function empresaDoHost(): ?Empresa
    {
        return \App\Support\EmpresaDominio::porHost(request()->getHost());
    }
}

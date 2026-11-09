<?php

namespace App\Services;

use App\Models\Aviso;
use App\Models\Empresa;
use App\Models\Etapa;
use App\Models\Evento;
use App\Support\SiteTema;
use Illuminate\Support\Str;

/**
 * Monta tudo que os modelos do site publico precisam: dados publicos
 * do tenant (sempre filtrados por empresa_id explicito, pois o site roda
 * sem contexto de tenant), paleta com contraste, CTAs com destinos reais,
 * navegacao apenas com secoes que existem e metadados de SEO.
 */
class SiteEmpresaService
{
    public function dados(Empresa $empresa, bool $preview = false): array
    {
        // Site publico: lista todas as unidades mesmo quando quem navega
        // e um usuario logado com unidades restritas (escopo 'escola').
        $escolas = $empresa->escolas()
            ->withoutGlobalScope('empresa')
            ->withoutGlobalScope('escola')
            ->where('empresa_id', $empresa->id)
            ->where('ativa', 'S')
            ->orderBy('nome')
            ->get(['id', 'empresa_id', 'nome', 'slug', 'email', 'telefone', 'endereco', 'cidade', 'estado', 'logo_path']);

        $avisos = Aviso::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->publicosSite()
            ->limit(4)
            ->get(['id', 'titulo', 'mensagem', 'tipo', 'data_publicacao']);

        $eventos = Evento::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->publicosSite()
            ->limit(5)
            ->get(['id', 'escola_id', 'titulo', 'tipo', 'data_inicio', 'data_fim', 'hora_inicio', 'local']);

        $etapas = Etapa::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('ativo', 'S')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get(['id', 'nome', 'segmento', 'descricao']);

        $whats = preg_replace('/\D/', '', (string) $empresa->site_whatsapp);
        $insta = ltrim((string) $empresa->site_instagram, '@/ ');
        $primeiraEscola = $escolas->first();
        $telefone = $empresa->telefone ?: $primeiraEscola?->telefone;
        $endereco = $empresa->endereco ?: $primeiraEscola?->endereco;

        $mostrarUnidades = $empresa->site_unidades === 'S' && $escolas->isNotEmpty();
        $statusMatricula = $escolas->isEmpty() ? null : match ($empresa->site_matricula) {
            'S' => 'aberta',
            'F' => 'encerrada',
            default => null,
        };
        $mostrarMatricula = $statusMatricula === 'aberta';
        $matriculaAno = trim((string) $empresa->site_matricula_ano) ?: null;
        $mostrarContato = $empresa->site_contato === 'S'
            && ($telefone || $empresa->email || $whats || $insta || $endereco);
        $temSobre = (bool) ($empresa->site_descricao || $empresa->site_imagem_sobre_url);

        // Escolas de outras unidades na agenda: identifica a unidade quando ha mais de uma.
        $nomesEscolas = $escolas->pluck('nome', 'id');
        $eventos->each(fn ($e) => $e->setAttribute('unidade', $escolas->count() > 1 ? $nomesEscolas[$e->escola_id] ?? null : null));

        $links = $this->links($empresa, $escolas, $whats, $insta, $endereco, $mostrarUnidades, $mostrarMatricula, $mostrarContato, $temSobre);
        $avisoSite = $this->aviso($empresa, $links, $statusMatricula, $matriculaAno);

        $paragrafos = collect(preg_split('/\R\s*\R|\R/', trim((string) $empresa->site_descricao)))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->values()
            ->all();
        $segmentosResumo = $etapas->pluck('nome')->implode(' · ');
        $temAcontece = $avisos->isNotEmpty() || $eventos->isNotEmpty();

        $secoes = array_filter([
            'sobre' => $temSobre ? 'A escola' : null,
            'segmentos' => $etapas->isNotEmpty() ? 'Ensino' : null,
            'unidades' => $mostrarUnidades ? 'Unidades' : null,
            'galeria' => $empresa->site_galeria_urls ? 'Estrutura' : null,
            'acontece' => $temAcontece ? ($eventos->isNotEmpty() ? 'Agenda' : 'Comunicados') : null,
            'contato' => $mostrarContato ? 'Contato' : null,
        ]);

        return [
            'empresa' => $empresa,
            'escolas' => $escolas,
            'avisos' => $avisos,
            'eventos' => $eventos,
            'etapas' => $etapas,
            'preview' => $preview,
            'tema' => SiteTema::paleta($empresa->cor_primaria, $empresa->cor_secundaria),
            'whats' => $whats,
            'insta' => $insta,
            'telefone' => $telefone,
            'endereco' => $endereco,
            'primeiraEscola' => $primeiraEscola,
            'localidade' => $this->localidade($escolas),
            'segmentosResumo' => $segmentosResumo,
            'heroLead' => $segmentosResumo ?: ($paragrafos ? Str::limit($paragrafos[0], 170) : null),
            'sobreParagrafos' => $paragrafos,
            'servicos' => $this->servicos($links, $temAcontece),
            'mostrarUnidades' => $mostrarUnidades,
            'mostrarMatricula' => $mostrarMatricula,
            'mostrarContato' => $mostrarContato,
            'temSobre' => $temSobre,
            'avisoSite' => $avisoSite,
            'matriculaStatus' => $statusMatricula,
            'matriculaAno' => $matriculaAno,
            'galeria' => $empresa->site_galeria_urls,
            'depoimentos' => $this->depoimentos($empresa),
            'secoes' => $secoes,
            'links' => $links,
            'ctaPrincipal' => $this->ctaPrincipal($links),
            'ctaSecundario' => $this->ctaSecundario($links),
            'seo' => $this->seo($empresa, $telefone, $endereco, $insta, $preview),
        ];
    }

    /** Destinos reais disponiveis — null quando a acao nao existe. */
    private function links(Empresa $empresa, $escolas, string $whats, string $insta, ?string $endereco, bool $unidades, bool $matricula, bool $contato, bool $sobre): array
    {
        $whatsIntl = $whats === '' ? null : (strlen($whats) >= 12 && str_starts_with($whats, '55') ? $whats : '55'.$whats);
        $urlWhats = fn (string $texto) => $whatsIntl ? 'https://wa.me/'.$whatsIntl.'?text='.rawurlencode($texto) : null;

        $matriculaUrl = null;
        if ($matricula) {
            $matriculaUrl = $escolas->count() === 1
                ? route('matricula_publica.create', [$empresa->slug, $escolas->first()->slug])
                : ($unidades ? '#unidades' : '#matricula');
        }

        return [
            'portal' => route('login'),
            'matricula' => $matriculaUrl,
            'matriculas' => $matricula
                ? $escolas->map(fn ($e) => ['nome' => $e->nome, 'url' => route('matricula_publica.create', [$empresa->slug, $e->slug])])->all()
                : [],
            'visita' => $urlWhats("Olá, {$empresa->nome}! Gostaria de agendar uma visita."),
            'whatsapp' => $urlWhats("Olá, {$empresa->nome}! Gostaria de mais informações."),
            'instagram' => $insta !== '' ? 'https://instagram.com/'.$insta : null,
            'mapa' => $endereco ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($endereco) : null,
            'contato' => $contato ? '#contato' : null,
            'sobre' => $sobre ? '#sobre' : null,
            'unidades' => $unidades ? '#unidades' : null,
            'trabalhe' => $empresa->email
                ? 'mailto:'.$empresa->email.'?subject='.rawurlencode('Trabalhe conosco — '.$empresa->nome)
                : null,
        ];
    }

    /**
     * Faixa de aviso: texto manual (site_aviso) tem prioridade; sem ele,
     * o periodo de matricula gera o aviso automaticamente ("Matriculas 2027 abertas/encerradas").
     */
    private function aviso(Empresa $empresa, array $links, ?string $statusMatricula, ?string $ano): ?array
    {
        if ($empresa->site_aviso) {
            $cta = $links['matricula']
                ? ['rotulo' => 'Matrícula online', 'url' => $links['matricula'], 'externo' => false]
                : ($links['visita']
                    ? ['rotulo' => 'Agende uma visita', 'url' => $links['visita'], 'externo' => true]
                    : null);

            return ['texto' => $empresa->site_aviso, 'cta' => $cta, 'tom' => 'info'];
        }

        if (! $statusMatricula) {
            return null;
        }

        $sufixo = $ano ? ' '.$ano : '';

        if ($statusMatricula === 'aberta') {
            return [
                'texto' => "Matrículas{$sufixo} abertas",
                'cta' => $links['matricula']
                    ? ['rotulo' => 'Matrícula online', 'url' => $links['matricula'], 'externo' => false]
                    : null,
                'tom' => 'aberta',
            ];
        }

        return [
            'texto' => "Matrículas{$sufixo} encerradas — acompanhe a abertura do próximo período",
            'cta' => $links['contato']
                ? ['rotulo' => 'Fale conosco', 'url' => $links['contato'], 'externo' => false]
                : ($links['visita']
                    ? ['rotulo' => 'Agende uma visita', 'url' => $links['visita'], 'externo' => true]
                    : null),
            'tom' => 'encerrada',
        ];
    }

    /** Uma unica acao principal: visita > matricula > contato > conhecer. */
    private function ctaPrincipal(array $links): ?array
    {
        return match (true) {
            (bool) $links['visita'] => ['rotulo' => 'Agende uma visita', 'url' => $links['visita'], 'icone' => 'fab fa-whatsapp', 'externo' => true],
            (bool) $links['matricula'] => ['rotulo' => 'Matrícula online', 'url' => $links['matricula'], 'icone' => 'fas fa-pen-to-square', 'externo' => false],
            (bool) $links['contato'] => ['rotulo' => 'Fale conosco', 'url' => $links['contato'], 'icone' => 'fas fa-phone', 'externo' => false],
            (bool) $links['unidades'] => ['rotulo' => 'Conheça nossas unidades', 'url' => $links['unidades'], 'icone' => 'fas fa-location-dot', 'externo' => false],
            default => null,
        };
    }

    private function ctaSecundario(array $links): ?array
    {
        if ($links['visita'] && $links['matricula']) {
            return ['rotulo' => 'Matrícula online', 'url' => $links['matricula'], 'externo' => false];
        }

        return $links['sobre'] ? ['rotulo' => 'Conheça a escola', 'url' => $links['sobre'], 'externo' => false] : null;
    }

    /** Servicos reais oferecidos as familias — cada um com destino existente. */
    private function servicos(array $links, bool $temAcontece): array
    {
        return array_values(array_filter([
            ['icone' => 'fas fa-user-graduate', 'titulo' => 'Portal do Aluno', 'texto' => 'Boletim, frequência e comunicados da escola em um só lugar.', 'url' => $links['portal'], 'externo' => false],
            $links['matricula'] ? ['icone' => 'fas fa-pen-to-square', 'titulo' => 'Matrícula online', 'texto' => 'Envie a solicitação de matrícula pelo site.', 'url' => $links['matricula'], 'externo' => false] : null,
            $links['whatsapp'] ? ['icone' => 'fab fa-whatsapp', 'titulo' => 'Atendimento pelo WhatsApp', 'texto' => 'Fale diretamente com a secretaria.', 'url' => $links['whatsapp'], 'externo' => true] : null,
            $temAcontece ? ['icone' => 'far fa-calendar', 'titulo' => 'Agenda e comunicados', 'texto' => 'Eventos, reuniões e avisos publicados pela escola.', 'url' => '#acontece', 'externo' => false] : null,
            $links['mapa'] ? ['icone' => 'fas fa-map-location-dot', 'titulo' => 'Como chegar', 'texto' => 'Veja o endereço e a rota até a escola.', 'url' => $links['mapa'], 'externo' => true] : null,
        ]));
    }

    private function localidade($escolas): ?string
    {
        $cidades = $escolas->filter(fn ($e) => $e->cidade)
            ->map(fn ($e) => $e->cidade.($e->estado ? '/'.$e->estado : ''))
            ->unique()
            ->values();

        return match (true) {
            $cidades->isEmpty() => null,
            $cidades->count() <= 2 => $cidades->implode(' e '),
            default => $cidades->count().' cidades',
        };
    }

    /** "Nome — papel | Texto" vira autor, papel e texto. */
    private function depoimentos(Empresa $empresa): array
    {
        return collect($empresa->depoimentos)
            ->filter(fn ($d) => $d['texto'] !== '')
            ->map(function ($d) {
                [$nome, $papel] = array_pad(array_map('trim', preg_split('/\s+[—–-]\s+/u', $d['nome'], 2)), 2, null);

                return ['nome' => $nome, 'papel' => $papel, 'texto' => $d['texto']];
            })
            ->values()
            ->all();
    }

    private function seo(Empresa $empresa, ?string $telefone, ?string $endereco, string $insta, bool $preview): array
    {
        $canonical = $empresa->dominio
            ? request()->getScheme().'://'.Str::of($empresa->dominio)->replaceMatches('#^https?://#', '')->trim('/').'/'
            : route('rede.site', $empresa->slug);

        $descricao = Str::limit(trim(strip_tags((string) ($empresa->site_descricao ?: $empresa->site_titulo))), 155, '…')
            ?: $empresa->nome;
        $imagem = $empresa->site_banner_url ?: $empresa->logo_url;
        $absoluta = fn (?string $url) => $url ? (Str::startsWith($url, 'http') ? $url : url($url)) : null;

        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'School',
            'name' => $empresa->nome,
            'url' => $canonical,
            'description' => $descricao,
            'logo' => $absoluta($empresa->logo_url),
            'image' => $absoluta($imagem),
            'telephone' => $telefone,
            'email' => $empresa->email,
            'address' => $endereco,
            'sameAs' => $insta !== '' ? ['https://instagram.com/'.$insta] : null,
        ]);

        return [
            'titulo' => $empresa->site_titulo ? $empresa->nome.' — '.Str::limit($empresa->site_titulo, 60, '') : $empresa->nome,
            'descricao' => $descricao,
            'canonical' => $canonical,
            'imagem' => $absoluta($imagem),
            'jsonLd' => $jsonLd,
            'robots' => $preview ? 'noindex, nofollow' : 'index, follow',
        ];
    }
}

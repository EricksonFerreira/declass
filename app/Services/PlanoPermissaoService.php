<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Permissao;
use App\Models\Plano;
use App\Models\User;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Collection;

class PlanoPermissaoService
{
    /**
     * Rotas internas/auxiliares que nao devem participar da logica de menu/permissao.
     *
     * @var string[]
     */
    private array $ignoredRoutePrefixes = [
        'login',
        'logout',
        'register',
        'password.',
        'verification.',
        'sanctum.',
        'ignition.',
        'debugbar.',
        'profile.',
        'onboarding.',
        'assinatura.',
        'menu.',
        'site.reserva.',
        'site.multa.',
    ];

    /**
     * Rotas sempre liberadas para usuarios autenticados.
     *
     * @var string[]
     */
    private array $alwaysAllowedRoutes = [
        'home',
        'dashboard',
        'home.chart-data',
        'site.apresentacao',
        'site.reserva.diasDisponiveisPorEstacao',
        'site.reserva.agendaDisponivelPorEstacao',
        'site.reserva.estacoesDisponiveis',
        'site.reserva.disponibilidade',
        'site.reserva.index',
        'reserva.minhas',
        'reserva.minhas.show',
        'reserva.minhas.multa.show',
        'reserva.minhas.assinar-checkin',
        'reserva.minhas.assinar-checkout',
        'reserva.minhas.salvar-assinatura-checkout',
        'reserva.minhas.vercontratocheckout',
        'reserva.minhas.vercontratocheckin',
        'reserva.minhas.satisfacao.store',
        'reserva.minhas.pagamento.efetuar',
        'reserva.minhas.salvarAssinarContratoCheckInMinhaReserva',
        'reserva.minhas.pagamento.confirmar',
        'reserva.minhas.motorista-app.show',
        'reserva.minhas.motorista-app.efetivar',
        'reserva.minhas.motorista-app.aceitar-termo',
        'reserva.minhas.motorista-app.assinar-contrato',
        'reserva.minhas.motorista-app.assinar-devolucao',
        'reserva.minhas.motorista-app.ver-contrato',
        'reserva.minhas.motorista-app.renovar',
        'reserva.minhas.motorista-app.cancelar',
        'reserva.show',
        'reserva.cancelar',
        'reserva.efetuarPagamento',
        'reserva.processarPagamento',
        'reserva.historico',
        'reserva.salvarAssinarContratoCheckIn',
        'reserva.salvarAssinaturaContratoCheckout',
        'tutoriais.index',
    ];

    public function planoAtivoDaEmpresa(?Empresa $empresa): ?Plano
    {
        if (! $empresa) {
            return null;
        }

        $query = $empresa->planoAtivo();

        if (SchemaCache::hasTable('plano_permissao')) {
            $query->with('plano.permissoes');
        } else {
            $query->with('plano');
        }

        return $query->first()?->plano;
    }

    public function permissoesPermitidasDoPlano(Plano $plano): Collection
    {
        if (! SchemaCache::hasTable('plano_permissao')) {
            return Permissao::query()
                ->where('guard_name', 'web')
                ->orderBy('grupo')
                ->orderBy('name')
                ->get();
        }

        return $plano->permissoes()
            ->where('guard_name', 'web')
            ->orderBy('grupo')
            ->orderBy('name')
            ->get();
    }

    public function idsPermitidosDoPlano(Plano $plano): array
    {
        $ids = $this->permissoesPermitidasDoPlano($plano)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! empty($ids)) {
            return $ids;
        }

        return Permissao::query()
            ->where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function quantidadeMaximaPorRegra(Plano $plano): ?int
    {
        return $plano->limite_permissoes_por_regra !== null
            ? (int) $plano->limite_permissoes_por_regra
            : null;
    }

    public function empresaPodeUsarPermissao(?Empresa $empresa, string $permissionName): bool
    {
        if ($permissionName === '') {
            return false;
        }

        $plano = $this->planoAtivoDaEmpresa($empresa);
        if (! $plano) {
            return false;
        }

        if (! SchemaCache::hasTable('plano_permissao')) {
            return true;
        }

        if (! $plano->permissoes()->exists()) {
            return true;
        }

        return $plano->permissoes()
            ->where('guard_name', 'web')
            ->where('name', $permissionName)
            ->exists();
    }

    public function usuarioPodeUsarPermissao(User $user, string $permissionName): bool
    {
        return $this->empresaPodeUsarPermissao($user->empresa, $permissionName);
    }

    public function usuarioPodeAcessarRota(User $user, string $routeName): bool
    {
        return $this->motivoBloqueioRota($user, $routeName) === null;
    }

    public function motivoBloqueioRota(User $user, string $routeName): ?string
    {
        if ($routeName === '' || $this->shouldIgnoreRoute($routeName)) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return null;
        }

        if (in_array($routeName, $this->alwaysAllowedRoutes, true)) {
            return null;
        }

        $moduleBlock = $this->checkPlanoModuleAccess($user, $routeName);
        if ($moduleBlock !== null) {
            return $moduleBlock;
        }

        if (! $user->hasPermissao($routeName)) {
            return 'Sem permissao para acessar esta acao.';
        }

        return null;
    }

    public function validarSelecaoDePermissoes(User $user, array $permissionIds): array
    {
        $permissionIds = collect($permissionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $plano = $this->planoAtivoDaEmpresa($user->empresa);
        if (! $plano) {
            return ['ok' => false, 'message' => 'A empresa precisa ter um plano ativo para configurar perfis de acesso.'];
        }

        $permitidos = $this->idsPermitidosDoPlano($plano);
        $invalidos = array_values(array_diff($permissionIds, $permitidos));

        if (! empty($invalidos)) {
            return ['ok' => false, 'message' => 'O plano atual nao permite conceder uma ou mais permissoes selecionadas.'];
        }

        $limite = $this->quantidadeMaximaPorRegra($plano);
        if ($limite !== null && count($permissionIds) > $limite) {
            return ['ok' => false, 'message' => "O plano atual permite no maximo {$limite} permissoes por perfil."];
        }

        return ['ok' => true, 'message' => null];
    }

    public function gruposDePermissoesParaTela(Collection $permissoes): array
    {
        return $permissoes
            ->groupBy(fn (Permissao $permissao) => $permissao->grupo_efetivo ?: '_outros')
            ->sortKeys()
            ->all();
    }

    private function checkPlanoModuleAccess(User $user, string $routeName): ?string
    {
        $empresa = $user->empresa ?? null;
        if (! $empresa) {
            return null;
        }

        $plano = $this->planoAtivoDaEmpresa($empresa);
        if (! $plano) {
            return null;
        }

        if (! $plano->modulo_multas && $this->routeIsInModule($routeName, ['multa'])) {
            return 'Seu plano não inclui o módulo de multas. Faça upgrade para acessar este recurso.';
        }

        if (! $plano->modulo_nfse && $this->routeIsInModule($routeName, ['nfse'])) {
            return 'Seu plano não inclui a emissão de NFS-e. Faça upgrade para acessar este recurso.';
        }

        if (! $plano->api_integracao && $this->routeIsInModule($routeName, ['api', 'webhook'])) {
            return 'Seu plano não inclui acesso à API. Faça upgrade para acessar este recurso.';
        }

        if (! $plano->site_vendas && $this->routeIsInModule($routeName, ['empresa.site-apresentacao'])) {
            return 'Seu plano não inclui o site de vendas. Faça upgrade para personalizar seu site.';
        }

        return null;
    }

    private function routeIsInModule(string $routeName, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rotas liberadas para qualquer usuario autenticado — espelha a regra do
     * middleware (prefixos ignorados + lista alwaysAllowed). Usado tambem por
     * hasPermissao/Gate para que itens como home e assinatura.* nao sumam do
     * menu quando a empresa esta sem plano ativo.
     */
    public function rotaSempreLiberada(string $routeName): bool
    {
        return $this->shouldIgnoreRoute($routeName)
            || in_array($routeName, $this->alwaysAllowedRoutes, true);
    }

    private function shouldIgnoreRoute(string $routeName): bool
    {
        foreach ($this->ignoredRoutePrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}

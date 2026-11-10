<?php

namespace App\Models;

use App\Notifications\ResetPasswordCustom;
use App\Notifications\VerifyEmailCustom;
use App\Services\PermissaoRotaService;
use App\Support\EspelhamentoContext;
use App\Support\SchemaCache;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'name',
        'email',
        'usuario',
        'telefone',
        'celular',
        'cidade',
        'numero_identificacao',
        'data_nascimento',
        'sexo',
        'foto_perfil',
        'cargo',
        'role_id',
        'tipo_usuario_id',
        'empresa_id',
        'forcar_troca_senha',
        'ativo',
        'password',
    ];

    /**
     * Tipos de conta de acesso: colaborador (equipe do tenant) ou
     * conta de portal vinculada a um aluno/responsavel.
     */
    public const TIPO_CONTA_COLABORADOR = 'colaborador';

    public const TIPO_CONTA_ALUNO = 'aluno';

    public const TIPO_CONTA_RESPONSAVEL = 'responsavel';

    public const TIPOS_CONTA = [
        self::TIPO_CONTA_COLABORADOR => 'Colaborador',
        self::TIPO_CONTA_ALUNO => 'Aluno',
        self::TIPO_CONTA_RESPONSAVEL => 'Responsável',
    ];

    /** Perfis (roles) que representam acesso ao Portal Familiar. */
    public const ROLE_ALUNO = 'aluno';

    public const ROLES_RESPONSAVEL = ['responsavel_legal', 'responsavel_financeiro'];

    public const ROLES_PORTAL = ['aluno', 'responsavel_legal', 'responsavel_financeiro'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'data_nascimento' => 'date',
        'password' => 'hashed',
        'forcar_troca_senha' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailCustom);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordCustom($token));
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nome'] ?? $this->attributes['name'] ?? null;
    }

    public function getFotoPerfilUrlAttribute(): string
    {
        if (! empty($this->foto_perfil) && Storage::disk('public')->exists($this->foto_perfil)) {
            return Storage::url($this->foto_perfil);
        }

        // Conta de portal sem foto propria: usa a foto cadastral da
        // pessoa vinculada (aluno ou responsavel) quando existir.
        if ($url = $this->vinculoPessoa()?->foto_url) {
            return $url;
        }

        $background = $this->normalizeHexColor($this->avatarBackgroundColor()) ?: 'FFFFFF';
        $foreground = $this->normalizeHexColor($this->avatarForegroundColor()) ?: $this->avatarForegroundColorFromBackground($background);

        return 'https://ui-avatars.com/api/?name='
            .urlencode((string) $this->nome)
            .'&background='.$background
            .'&color='.$foreground
            .'&size=256';
    }

    private function avatarBackgroundColor(): string
    {
        $empresaCorPrimaria = trim((string) ($this->empresa?->cor_primaria ?? ''));

        return $empresaCorPrimaria !== '' ? $empresaCorPrimaria : '#FFFFFF';
    }

    private function avatarForegroundColor(): string
    {
        $empresaCorSecundaria = trim((string) ($this->empresa?->cor_secundaria ?? ''));

        return $empresaCorSecundaria !== '' ? $empresaCorSecundaria : '';
    }

    private function avatarForegroundColorFromBackground(string $background): string
    {
        $hex = $this->normalizeHexColor($background);

        if ($hex === '') {
            return '#0F172A';
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $rl = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $gl = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $bl = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        $luminance = 0.2126 * $rl + 0.7152 * $gl + 0.0722 * $bl;

        return $luminance > 0.58 ? '#0F172A' : '#FFFFFF';
    }

    private function normalizeHexColor(string $color): string
    {
        $color = strtoupper(trim($color));

        if ($color === '') {
            return '';
        }

        if ($color[0] === '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) === 3 && ctype_xdigit($color)) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        }

        if (strlen($color) !== 6 || ! ctype_xdigit($color)) {
            return '';
        }

        return $color;
    }

    public function adminlte_image(): string
    {
        return $this->foto_perfil_url;
    }

    public function tipoUsuario(): BelongsTo
    {
        return $this->belongsTo(Regra::class, 'role_id');
    }

    /**
     * Alias historicamente usado em muitos pontos do sistema.
     */
    public function regra(): BelongsTo
    {
        return $this->belongsTo(Regra::class, 'role_id');
    }

    public function getTipoUsuarioIdAttribute(): mixed
    {
        return $this->role_id;
    }

    public function setTipoUsuarioIdAttribute($value): void
    {
        $this->attributes['role_id'] = $value;
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Escolas/unidades onde o usuario atua (vinculo N:N).
     */
    public function escolas(): BelongsToMany
    {
        return $this->belongsToMany(Escola::class, 'user_escolas', 'user_id', 'escola_id')
            ->withTimestamps();
    }

    /**
     * Registro de aluno vinculado a esta conta (acesso do proprio
     * aluno ao Portal Familiar).
     */
    public function aluno(): HasOne
    {
        return $this->hasOne(Aluno::class, 'user_id');
    }

    /**
     * Registro de responsavel vinculado a esta conta (acesso da
     * familia ao Portal Familiar).
     */
    public function responsavel(): HasOne
    {
        return $this->hasOne(Responsavel::class, 'user_id');
    }

    /**
     * Tipo da conta: 'aluno' quando vinculada a um registro de aluno
     * (ou perfil 'aluno'), 'responsavel' quando vinculada a um
     * responsavel (ou perfil responsavel_*), senao 'colaborador'.
     */
    public function tipoConta(): string
    {
        $roleName = strtolower(trim((string) ($this->regra?->name ?? '')));

        if ($this->temVinculo('aluno') || $roleName === self::ROLE_ALUNO) {
            return self::TIPO_CONTA_ALUNO;
        }

        if ($this->temVinculo('responsavel') || in_array($roleName, self::ROLES_RESPONSAVEL, true)) {
            return self::TIPO_CONTA_RESPONSAVEL;
        }

        return self::TIPO_CONTA_COLABORADOR;
    }

    public function tipoContaLabel(): string
    {
        return self::TIPOS_CONTA[$this->tipoConta()] ?? self::TIPOS_CONTA[self::TIPO_CONTA_COLABORADOR];
    }

    /** Nome do aluno/responsavel vinculado a conta, quando houver. */
    public function vinculoNome(): ?string
    {
        return $this->aluno?->nome ?? $this->responsavel?->nome;
    }

    /** Pessoa do aluno/responsavel vinculado a conta, quando houver. */
    private function vinculoPessoa(): ?Pessoa
    {
        return $this->aluno?->pessoa ?? $this->responsavel?->pessoa;
    }

    /**
     * Conta de portal: o perfil e de aluno/responsavel e a home
     * operacional nao se aplica — o destino pos-login e o Portal.
     */
    public function isPortalAccount(): bool
    {
        $roleName = strtolower(trim((string) ($this->regra?->name ?? '')));

        return in_array($roleName, self::ROLES_PORTAL, true);
    }

    /**
     * Conta com perfil restrito: no Meu Perfil so pode alterar a
     * propria foto — aluno, responsavel, professor, secretaria e
     * direcao. Admins e demais equipes editam dados de contato.
     */
    public function perfilApenasFoto(): bool
    {
        return $this->isPortalAccount() || $this->isProfessor()
            || $this->isSecretaria() || $this->isDiretor();
    }

    /**
     * Ha alunos acessiveis pela conta no Portal Familiar: o proprio
     * aluno ou os alunos do responsavel vinculado.
     */
    public function temAlunosVinculados(): bool
    {
        if ($this->aluno()->exists()) {
            return true;
        }

        $responsavel = $this->responsavel()->first(['id']);

        return $responsavel ? $responsavel->alunos()->exists() : false;
    }

    private function temVinculo(string $relation): bool
    {
        return $this->relationLoaded($relation)
            ? $this->getRelation($relation) !== null
            : $this->{$relation}()->exists();
    }

    /**
     * Escolas que o usuario pode selecionar no contexto da sessao.
     * Retorna null quando o usuario nao tem vinculos — nesse caso
     * ele pode alternar entre todas as escolas do tenant.
     *
     * Professor e excecao: so alterna entre as unidades onde leciona
     * (atribuicao ativa em turma); os vinculos em user_escolas, quando
     * existem, seguem valendo como restricao adicional.
     *
     * @return array<int, int>|null
     */
    /** @var false|array<int, int>|null */
    private $escolasContextoIdsResolvido = false;

    public function escolasContextoIds(): ?array
    {
        // Memoizado por instancia: o escopo global 'escola' chama este
        // metodo em toda consulta da tabela escolas.
        if ($this->escolasContextoIdsResolvido !== false) {
            return $this->escolasContextoIdsResolvido;
        }

        // Espelhamento: as escolas avaliadas sao as do usuario espelhado.
        $efetivo = ($this->isSuperAdmin() ? $this->alvoEspelhado() : null) ?? $this;

        // DB::table em vez do relacionamento: o escopo global 'escola'
        // agora tambem limita a tabela escolas pelas unidades permitidas
        // — consulta-lo aqui causaria recursao.
        $vinculadas = DB::table('user_escolas')
            ->where('user_id', $efetivo->id)
            ->pluck('escola_id')
            ->map(fn ($id) => (int) $id);

        $permitidas = null;
        if ($efetivo->isProfessor() && SchemaCache::hasTable('atribuicoes')) {
            // Sem o escopo global de escola: o filtro da sessao nao pode
            // esconder as demais unidades onde o professor leciona.
            $leciona = Atribuicao::query()
                ->withoutGlobalScope('escola')
                ->where('user_id', $efetivo->id)
                ->where('ativo', 'S')
                ->whereNotNull('escola_id')
                ->pluck('escola_id')
                ->map(fn ($id) => (int) $id)
                ->unique();

            $permitidas = ($vinculadas->isEmpty() ? $leciona : $leciona->intersect($vinculadas))->values()->all();
        } elseif ($vinculadas->isNotEmpty()) {
            $permitidas = $vinculadas->all();
        }

        return $this->escolasContextoIdsResolvido = $permitidas;
    }

    /**
     * Perfis de docencia: so enxergam/regem os proprios registros
     * pedagogicos (diarios, avaliacoes) em vez do escopo completo.
     */
    public function isProfessor(): bool
    {
        // Espelhamento: o superadmin ve o papel do usuario espelhado.
        if ($this->isSuperAdmin() && ($alvo = $this->alvoEspelhado())) {
            return $alvo->isProfessor();
        }

        $role = strtolower(trim((string) ($this->regra?->name ?? '')));

        return in_array($role, ['professor', 'professor_substituto'], true);
    }

    public function isSecretaria(): bool
    {
        // Espelhamento: o superadmin ve o papel do usuario espelhado.
        if ($this->isSuperAdmin() && ($alvo = $this->alvoEspelhado())) {
            return $alvo->isSecretaria();
        }

        $role = strtolower(trim((string) ($this->regra?->name ?? '')));

        return in_array($role, ['secretario', 'auxiliar_secretaria'], true);
    }

    /**
     * Direcao escolar: diretor e vice-diretor.
     */
    public function isDiretor(): bool
    {
        // Espelhamento: o superadmin ve o papel do usuario espelhado.
        if ($this->isSuperAdmin() && ($alvo = $this->alvoEspelhado())) {
            return $alvo->isDiretor();
        }

        $role = strtolower(trim((string) ($this->regra?->name ?? '')));

        return in_array($role, ['diretor', 'vice_diretor'], true);
    }

    public function isAdministrador(): bool
    {
        if ($this->isSuperAdmin()) {
            if ($alvo = $this->alvoEspelhado()) {
                return $alvo->isAdministrador();
            }

            return true;
        }

        $roleName = strtolower(trim((string) ($this->regra?->name ?? '')));

        return in_array($roleName, ['administrador', 'admin'], true);
    }

    /**
     * Super admin da plataforma: tipo_usuario_id = 1.
     * So pode ser definido via UPDATE direto no banco — o mutator
     * setTipoUsuarioIdAttribute redireciona escritas Eloquent para role_id.
     */
    public function isSuperAdmin(): bool
    {
        return (int) ($this->getRawOriginal('tipo_usuario_id') ?? 0) === 1;
    }

    /**
     * Alvo do espelhamento ativo — so existe para superadmin; para
     * qualquer outro usuario (inclusive o proprio alvo) retorna null,
     * o que evita recursao quando o alvo avalia as suas permissoes.
     */
    private function alvoEspelhado(): ?self
    {
        $contexto = app(EspelhamentoContext::class);

        return $contexto->ativo() ? $contexto->alvo() : null;
    }

    public function hasPermissao(string $permissao): bool
    {
        if ($this->isSuperAdmin()) {
            // Espelhamento: avalia a permissao do usuario espelhado,
            // mantendo a identidade (e a auditoria) do superadmin.
            if ($alvo = $this->alvoEspelhado()) {
                return $alvo->hasPermissao($permissao);
            }

            return true;
        }

        if ($permissao === '') {
            return false;
        }

        // Rotas liberadas para qualquer autenticado nao dependem de perfil.
        if (app(PermissaoRotaService::class)->rotaSempreLiberada($permissao)) {
            return true;
        }

        // Negacao direta do superadmin tem prioridade sobre o perfil.
        $negada = DB::table('user_permission_denies')
            ->join('permissions', 'user_permission_denies.permission_id', '=', 'permissions.id')
            ->where('user_permission_denies.user_id', $this->id)
            ->where('permissions.name', $permissao)
            ->exists();
        if ($negada) {
            return false;
        }

        // Concessao direta (permissoes individuais além do perfil).
        $concedida = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $this->id)
            ->where('model_has_permissions.model_type', self::class)
            ->where('permissions.name', $permissao)
            ->exists();
        if ($concedida) {
            return true;
        }

        if (! $this->role_id) {
            // Administrador legado sem perfil vinculado mantem acesso total
            // da empresa; os demais nao tem de onde herdar permissao.
            return $this->isAdministrador();
        }

        $hasPermission = DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('permissions.name', $permissao)
            ->where('permissions.guard_name', 'web')
            ->where('roles.id', $this->role_id)
            ->exists();

        return $hasPermission;
    }

    public function firstPermittedRouteNameForRedirect(): ?string
    {
        if ($this->isAdministrador()) {
            return 'home';
        }

        if (! $this->role_id) {
            return null;
        }

        $permissions = DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('permissions.guard_name', 'web')
            ->where('roles.id', $this->role_id)
            ->pluck('permissions.name')
            ->map(fn ($name) => (string) $name)
            ->unique()
            ->values();

        if ($permissions->isEmpty()) {
            return null;
        }

        $candidates = $permissions->filter(function (string $routeName) {
            if (! Route::has($routeName)) {
                return false;
            }

            $route = Route::getRoutes()->getByName($routeName);
            if (! $route) {
                return false;
            }

            $methods = $route->methods();
            $hasGet = in_array('GET', $methods, true) || in_array('HEAD', $methods, true);
            if (! $hasGet) {
                return false;
            }

            return count($route->parameterNames()) === 0;
        })->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        foreach (['home', 'dashboard'] as $preferred) {
            if ($candidates->contains($preferred)) {
                return $preferred;
            }
        }

        $priorityActions = ['index', 'filtro', 'setPerPage', 'show', 'create'];
        foreach ($priorityActions as $action) {
            $match = $candidates->first(fn (string $name) => str_ends_with($name, '.'.$action));
            if ($match) {
                return $match;
            }
        }

        return $candidates->first();
    }
}

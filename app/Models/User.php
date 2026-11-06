<?php

namespace App\Models;

use App\Notifications\ResetPasswordCustom;
use App\Notifications\VerifyEmailCustom;
use App\Services\PermissaoRotaService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * Escolas que o usuario pode selecionar no contexto da sessao.
     * Retorna null quando o usuario nao tem vinculos — nesse caso
     * ele pode alternar entre todas as escolas do tenant.
     *
     * @return array<int, int>|null
     */
    public function escolasContextoIds(): ?array
    {
        $ids = $this->escolas()->pluck('escolas.id')->map(fn ($id) => (int) $id)->all();

        return $ids === [] ? null : $ids;
    }

    /**
     * Perfis de docencia: so enxergam/regem os proprios registros
     * pedagogicos (diarios, avaliacoes) em vez do escopo completo.
     */
    public function isProfessor(): bool
    {
        $role = strtolower(trim((string) ($this->regra?->name ?? '')));

        return in_array($role, ['professor', 'professor_substituto'], true);
    }

    public function isAdministrador(): bool
    {
        if ($this->isSuperAdmin()) {
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

    public function hasPermissao(string $permissao): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($permissao === '') {
            return false;
        }

        // Rotas liberadas para qualquer autenticado nao dependem de perfil.
        if (app(PermissaoRotaService::class)->rotaSempreLiberada($permissao)) {
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

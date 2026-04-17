<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use App\Notifications\VerifyEmailCustom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'email',
        'sexo',
        'data_nascimento',
        'local_nascimento',
        'telefone',
        'celular',
        'pais_sigla',
        'zip_code',
        'cidade',
        'endereco',
        'numero_identificacao',
        'role_id',
        'stripe_id',
        'password',
        'numero_carta_conducao',
        'local_emissao_carta_conducao',
        'pais_emissao_carta_conducao',
        'data_emissao_carta_conducao',
        'data_validade_carta_conducao',
        'empresa_id',
        'acesso_todas_estacoes',
        'forcar_troca_senha',
        'foto_perfil',
        'saldo_atual',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'acesso_todas_estacoes' => 'boolean',
        'forcar_troca_senha' => 'boolean',
        'saldo_atual' => 'decimal:2',
    ];

    public function sendEmailVerificationNotification()
{
    $this->notify(new VerifyEmailCustom);
}

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nome'] ?? null;
    }

    public function getFotoPerfilUrlAttribute(): string
    {
        if (!empty($this->foto_perfil) && Storage::disk('public')->exists($this->foto_perfil)) {
            return Storage::url($this->foto_perfil);
        }

        $background = $this->normalizeHexColor($this->avatarBackgroundColor()) ?: '1F2937';
        $foreground = $this->normalizeHexColor($this->avatarForegroundColor()) ?: $this->avatarForegroundColorFromBackground($background);

        return 'https://ui-avatars.com/api/?name='
            . urlencode((string) $this->nome)
            . '&background=' . $background
            . '&color=' . $foreground
            . '&size=256';
    }

    private function avatarBackgroundColor(): string
    {
        $empresaCorPrimaria = trim((string) ($this->empresa?->cor_primaria ?? ''));

        return $empresaCorPrimaria !== '' ? $empresaCorPrimaria : '#1F2937';
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
            return '#FFFFFF';
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
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        if (strlen($color) !== 6 || !ctype_xdigit($color)) {
            return '';
        }

        return $color;
    }

    public function adminlte_image(): string
    {
        return $this->foto_perfil_url;
    }

    public function tipoUsuario()
    {
        return $this->belongsTo(Regra::class, 'role_id');
    }

    /**
     * Alias historicamente usado em muitos pontos do sistema.
     * Mantemos `regra()` para compatibilidade com código que usa `$user->regra`.
     */
    public function regra()
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function estacoesPermitidas()
    {
        return $this->belongsToMany(Estacao::class, 'estacao_user', 'user_id', 'estacao_id')->withTimestamps();
    }

    public function hasAcessoTodasEstacoes(): bool
    {
        if ($this->isAdministrador()) {
            return true;
        }

        if (!Schema::hasColumn($this->getTable(), 'acesso_todas_estacoes')) {
            return true;
        }

        return (bool) ($this->acesso_todas_estacoes ?? true);
    }

    public function estacoesPermitidasIds(): array
    {
        if ($this->hasAcessoTodasEstacoes()) {
            return [];
        }

        return $this->estacoesPermitidas()->pluck('estacoes.id')->map(fn ($id) => (int) $id)->all();
    }

    public function isAdministrador(): bool
    {
        $roleId = (int) ($this->role_id ?? 0);
        $tipoUsuarioIdRaw = (int) ($this->getRawOriginal('tipo_usuario_id') ?? 0);

        return $roleId === 1 || $tipoUsuarioIdRaw === 1;
    }

    /**
     * Super admin do sistema (global): tipo_usuario_id = 1.
     * Possui acesso total, independente da empresa.
     */
    public function isSuperAdmin(): bool
    {
        return (int) ($this->getRawOriginal('tipo_usuario_id') ?? 0) === 1;
    }

    public function hasPermissao(string $permissao): bool
    {
        if ($this->isAdministrador()) {
            return true;
        }

        if (!$this->role_id || $permissao === '') {
            return false;
        }

        // Permissões e roles são defaults globais do sistema (sem empresa_id).
        return DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('permissions.name', $permissao)
            ->where('permissions.guard_name', 'web')
            ->where('roles.id', $this->role_id)
            ->exists();
    }

    public function firstPermittedRouteNameForRedirect(): ?string
    {
        if ($this->isAdministrador()) {
            return 'home';
        }

        if (!$this->role_id) {
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
            if (!Route::has($routeName)) {
                return false;
            }

            $route = Route::getRoutes()->getByName($routeName);
            if (!$route) {
                return false;
            }

            $methods = $route->methods();
            $hasGet = in_array('GET', $methods, true) || in_array('HEAD', $methods, true);
            if (!$hasGet) {
                return false;
            }

            return count($route->parameterNames()) === 0;
        })->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        // Prioriza telas de listagem/entrada.
        foreach (['home', 'dashboard'] as $preferred) {
            if ($candidates->contains($preferred)) {
                return $preferred;
            }
        }

        $priorityActions = ['index', 'filtro', 'setPerPage', 'show', 'create'];
        foreach ($priorityActions as $action) {
            $match = $candidates->first(fn (string $name) => str_ends_with($name, '.' . $action));
            if ($match) {
                return $match;
            }
        }

        return $candidates->first();
    }
}

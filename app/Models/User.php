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

        return 'https://ui-avatars.com/api/?name=' . urlencode((string) $this->nome) . '&background=0D8ABC&color=fff&size=256';
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

    public function hasPermissao(string $permissao): bool
    {
        if ($this->isAdministrador()) {
            return true;
        }

        if (!$this->empresa_id || !$this->role_id || $permissao === '') {
            return false;
        }

        // Permissões são globais (sem empresa_id); roles pertencem à empresa do usuário
        return DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('permissions.name', $permissao)
            ->where('permissions.guard_name', 'web')
            ->where('roles.id', $this->role_id)
            ->where('roles.empresa_id', $this->empresa_id)
            ->exists();
    }

    public function firstPermittedRouteNameForRedirect(): ?string
    {
        if ($this->isAdministrador()) {
            return 'home';
        }

        if (!$this->empresa_id || !$this->role_id) {
            return null;
        }

        $permissions = DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('permissions.guard_name', 'web')
            ->where('roles.id', $this->role_id)
            ->where('roles.empresa_id', $this->empresa_id)
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

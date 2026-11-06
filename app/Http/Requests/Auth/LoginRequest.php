<?php

namespace App\Http\Requests\Auth;

use App\Models\Empresa;
use App\Models\User;
use App\Support\EmpresaDominio;
use App\Support\SchemaCache;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Slug reservado da plataforma: nao pode ser registrado como
     * tenant e da acesso ao superadmin (usuario sem empresa).
     */
    public const SLUG_PLATAFORMA = 'declass';

    private ?Empresa $resolvedEmpresa = null;

    private ?User $resolvedSuperadmin = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug', ''));
        if ($slug === '') {
            $slug = $this->slugFromDomain() ?? '';
        }

        $this->merge([
            'slug' => Str::lower($slug),
            'email' => Str::lower(trim((string) $this->input('email', ''))),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Slug da plataforma: autentica o superadmin (sem tenant).
        if ($this->isSlugPlataforma()) {
            $user = $this->resolveSuperadmin();

            if (! $user || ! Hash::check((string) $this->input('password'), (string) $user->password)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            Auth::login($user, $this->boolean('remember'));
            RateLimiter::clear($this->throttleKey());

            return;
        }

        $empresa = $this->resolveEmpresa();
        $user = $empresa ? $this->resolveUserForEmpresa($empresa->id) : null;

        if (! $user || ! Hash::check((string) $this->input('password'), (string) $user->password)) {
            RateLimiter::hit($this->throttleKey());

            $messages = [
                'email' => trans('auth.failed'),
            ];

            if (! $empresa) {
                $messages['slug'] = ['Empresa não encontrada para o slug informado.'];
            }

            throw ValidationException::withMessages($messages);
        }

        Auth::login($user, $this->boolean('remember'));

        if ($empresa) {
            $this->session()->put('empresa_context_id', (int) $empresa->id);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('adminlte::auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $empresa = $this->resolveEmpresa();

        return Str::transliterate(
            ($empresa?->id ? 'empresa:'.$empresa->id : 'slug:'.Str::lower($this->string('slug'))).'|'
            .Str::lower($this->string('email')).'|'.$this->ip()
        );
    }

    private function resolveEmpresa(): ?Empresa
    {
        if ($this->resolvedEmpresa !== null) {
            return $this->resolvedEmpresa;
        }

        $this->resolvedEmpresa = $this->resolveEmpresaBySlug() ?? $this->resolveEmpresaByDomain();

        return $this->resolvedEmpresa;
    }

    private function resolveEmpresaBySlug(): ?Empresa
    {
        if (! SchemaCache::hasTable('empresas')) {
            return null;
        }

        $slug = trim((string) $this->input('slug', ''));
        if ($slug === '') {
            return null;
        }

        return Empresa::query()
            ->withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('ativo', 'S')
            ->first();
    }

    private function resolveEmpresaByDomain(): ?Empresa
    {
        return EmpresaDominio::porHost($this->getHost());
    }

    private function resolveUserForEmpresa(int $empresaId): ?User
    {
        // Aceita e-mail ou nome de usuario (coluna users.usuario, quando existe).
        $login = Str::lower(trim((string) $this->input('email', '')));
        if ($login === '') {
            return null;
        }

        $hasUsuario = SchemaCache::hasColumn('users', 'usuario');

        return User::query()
            ->where(function ($query) use ($login, $hasUsuario) {
                $query->where('email', $login);

                if ($hasUsuario) {
                    $query->orWhere('usuario', $login);
                }
            })
            ->where(function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->first();
    }

    private function slugFromDomain(): ?string
    {
        return $this->resolveEmpresaByDomain()?->slug;
    }

    private function isSlugPlataforma(): bool
    {
        return Str::lower(trim((string) $this->input('slug', ''))) === self::SLUG_PLATAFORMA;
    }

    /**
     * Superadmin da plataforma: tipo_usuario_id = 1, sem empresa.
     * Login por e-mail ou usuario alternativo.
     */
    private function resolveSuperadmin(): ?User
    {
        if ($this->resolvedSuperadmin !== null) {
            return $this->resolvedSuperadmin;
        }

        if (! SchemaCache::hasTable('users')) {
            return $this->resolvedSuperadmin = null;
        }

        $login = Str::lower(trim((string) $this->input('email', '')));
        if ($login === '') {
            return $this->resolvedSuperadmin = null;
        }

        $hasUsuario = SchemaCache::hasColumn('users', 'usuario');

        return $this->resolvedSuperadmin = User::query()
            ->where('tipo_usuario_id', 1)
            ->where(function ($query) use ($login, $hasUsuario) {
                $query->where('email', $login);

                if ($hasUsuario) {
                    $query->orWhere('usuario', $login);
                }
            })
            ->first();
    }
}

<?php

namespace App\Http\Requests\Auth;

use App\Models\Empresa;
use App\Models\User;
use App\Support\DomainNormalizer;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
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
            'email' => ['required', 'string', 'email'],
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

        $empresa = $this->resolveEmpresaBySlug();
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
            'email' => trans('adminlte.auth.throttle', [
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
        return Str::transliterate(
            Str::lower($this->string('slug')).'|'.Str::lower($this->string('email')).'|'.$this->ip()
        );
    }

    private function resolveEmpresaBySlug(): ?Empresa
    {
        if (!Schema::hasTable('empresas')) {
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

    private function resolveUserForEmpresa(int $empresaId): ?User
    {
        $email = Str::lower(trim((string) $this->input('email', '')));
        if ($email === '') {
            return null;
        }

        return User::query()
            ->where('email', $email)
            ->where(function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->first();
    }

    private function slugFromDomain(): ?string
    {
        if (!Schema::hasTable('empresas')) {
            return null;
        }

        $host = DomainNormalizer::normalize($this->getHost());
        if (!$host) {
            return null;
        }

        $empresa = Empresa::query()
            ->withoutGlobalScopes()
            ->where('ativo', 'S')
            ->whereNotNull('dominio')
            ->get(['id', 'slug', 'dominio'])
            ->first(function (Empresa $empresa) use ($host) {
                return DomainNormalizer::normalize($empresa->dominio) === $host;
            });

        return $empresa?->slug;
    }
}

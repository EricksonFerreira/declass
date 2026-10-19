<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    use HasFactory;

    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'guard_name',
        'descricao',
        'grupo',
        // empresa_id intencionalmente omitido — permissões são globais
    ];

    /**
     * Permissoes internas do sistema — nao sao gerenciadas por perfil:
     * nao aparecem na edicao de regras, nao sao gravadas em roles e nao
     * passam pela checagem de permissao (as rotas tem protecao propria:
     * superadmin, token de cron, assinatura do gateway etc.).
     */
    public const PREFIXOS_INTERNOS = [
        'planos', 'pricing', 'onboarding', 'migrations', 'log-viewer',
        'limpar-cache', 'ia-global', 'configuracao-ia', 'comandos',
        'cadastro', 'assinatura.pagamento',
        'site', 'empresa-contexto', 'empresa_context', 'cashier',
        'darkmode', 'adminlte',
    ];

    // Trechos que casam em qualquer posicao do nome (ex.: as rotas de
    // edicao do site sao empresa.site-editor.*, nao comecam com "site").
    private const TRECHOS_INTERNOS = ['site-apresentacao', 'site-editor'];

    // Prefixos que so casam com "." depois (ex.: "cron" nao pode
    // capturar "cronogramas").
    private const PREFIXOS_INTERNOS_PONTO = ['cron'];

    public static function isNomeInterno(string $name): bool
    {
        foreach (self::PREFIXOS_INTERNOS as $prefixo) {
            if (str_starts_with($name, $prefixo)) {
                return true;
            }
        }

        foreach (self::TRECHOS_INTERNOS as $trecho) {
            if (str_contains($name, $trecho)) {
                return true;
            }
        }

        foreach (self::PREFIXOS_INTERNOS_PONTO as $prefixo) {
            if ($name === $prefixo || str_starts_with($name, $prefixo.'.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retorna o grupo derivado automaticamente do nome da permissão se não definido.
     */
    public function getGrupoEfetivoAttribute(): string
    {
        if (!empty($this->grupo)) {
            return $this->grupo;
        }
        if (str_contains($this->name, '.')) {
            return (string) str($this->name)->before('.');
        }
        return $this->name;
    }

    public function regras()
    {
        return $this->belongsToMany(Regra::class, 'role_has_permissions', 'permission_id', 'role_id');
    }

    public function planos()
    {
        return $this->belongsToMany(Plano::class, 'plano_permissao', 'permission_id', 'plano_id')->withTimestamps();
    }
}

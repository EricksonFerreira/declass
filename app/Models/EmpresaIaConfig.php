<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Configuração de IA por empresa: provedor padrão, chaves por provedor
 * (criptografadas), limite de tokens, anonização LGPD e instruções
 * extras aplicadas em todos os prompts.
 */
class EmpresaIaConfig extends Model
{
    public const PROVEDORES = [
        'deepseek' => 'DeepSeek',
        'openai' => 'OpenAI (ChatGPT)',
    ];

    public const TAMANHOS_RESUMO = [
        'curto' => 'Curto e objetivo',
        'medio' => 'Equilibrado',
        'detalhado' => 'Detalhado',
    ];

    protected $table = 'empresa_ia_configs';

    protected $fillable = [
        'empresa_id',
        'provedor_padrao',
        'tokens_limite',
        'anonimizar',
        'tamanho_resumo',
        'instrucoes_extras',
        'deepseek_key',
        'deepseek_model',
        'openai_key',
        'openai_model',
        'usuinc',
        'usualt',
    ];

    protected $casts = [
        'tokens_limite' => 'integer',
        'deepseek_key' => 'encrypted',
        'openai_key' => 'encrypted',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /** API key efetiva do provedor: da empresa, senão a global do .env. */
    public function chaveDo(string $provedor): string
    {
        $campo = $provedor.'_key';

        return (string) ($this->{$campo} ?? '') !== ''
            ? (string) $this->{$campo}
            : (string) config("services.{$provedor}.key", '');
    }

    public function modeloDo(string $provedor): string
    {
        $campo = $provedor.'_model';

        return (string) ($this->{$campo} ?? '') !== ''
            ? (string) $this->{$campo}
            : (string) config("services.{$provedor}.model", '');
    }
}

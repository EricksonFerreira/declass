<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Configuração global de IA da plataforma (linha única). Padrão para
 * empresas sem config própria e provedor do futuro assistente/chat.
 * Gerenciada pelo superadmin em Configurações → Integração com IA.
 */
class IaConfigGlobal extends Model
{
    public const TRANSCRICAO_PROVEDORES = [
        '' => 'Mesmo provedor principal',
        'openai' => 'OpenAI — whisper-1',
        'groq' => 'Groq — whisper-large-v3-turbo',
    ];

    protected $table = 'ia_config_global';

    protected $fillable = [
        'provedor',
        'api_key',
        'modelo',
        'assistente_ativo',
        'transcricao_provedor',
        'transcricao_key',
        'transcricao_modelo',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'transcricao_key' => 'encrypted',
    ];

    public static function atual(): self
    {
        return self::firstOrNew(['id' => 1]);
    }
}

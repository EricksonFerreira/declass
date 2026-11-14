<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Pessoa: entidade base de identidade (dados pessoais).
 * Alunos e responsaveis referenciam uma pessoa — os dados
 * academicos/financeiros ficam nos modelos especializados.
 */
class Pessoa extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'nome',
        'nome_social',
        'cpf',
        'rg',
        'data_nascimento',
        'sexo',
        'email',
        'telefone',
        'celular',
        'cep',
        'endereco',
        'cidade',
        'estado',
        'foto_path',
        'ativo',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public const SEXOS = [
        'F' => 'Feminino',
        'M' => 'Masculino',
        'O' => 'Outro',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class, 'pessoa_id');
    }

    public function responsaveis(): HasMany
    {
        return $this->hasMany(Responsavel::class, 'pessoa_id');
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }

    /** Atalho de leitura em Blade: $model->esta_ativa */
    public function getEstaAtivaAttribute()
    {
        return $this->estaAtiva();
    }

    public function nomeExibicao(): string
    {
        return trim((string) ($this->nome_social ?: $this->nome));
    }

    /** Atalho de leitura em Blade: $model->nome_exibicao */
    public function getNomeExibicaoAttribute()
    {
        return $this->nomeExibicao();
    }

    /**
     * URL da foto cadastral; null quando nao ha arquivo no disco
     * publico — a view decide o fallback (iniciais, icone etc.).
     */
    public function getFotoUrlAttribute(): ?string
    {
        $path = trim((string) ($this->foto_path ?? ''));

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::url($path);
    }
}

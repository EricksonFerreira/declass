<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function nomeExibicao(): string
    {
        return trim((string) ($this->nome_social ?: $this->nome));
    }
}

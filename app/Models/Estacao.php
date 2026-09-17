<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estacao extends Model
{
    protected $table = 'estacoes';

    protected $fillable = [
        'nome',
        'dia_util_id',
        'endereco',
        'numero',
        'cidade',
        'nfse_municipio_codigo',
        'nfse_inscricao_municipal',
        'codigo_postal',
        'pais_id',
        'latitude',
        'longitude',
        'telefone',
        'celular',
        'zip_code',
        'email',
        'observacoes',
        'cobrar_na_coleta',
        'valor_taxa_coleta',
        'cobrar_na_entrega',
        'valor_taxa_entrega',
        'valido_no_intervalo',
        'ativo',
        'checkin',
        'checkout',
        'empresa_id',
        'datainc',
        'usuinc',
        'datatu',
        'usuatu',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'checkin' => 'boolean',
        'checkout' => 'boolean',
        'cobrar_na_coleta' => 'boolean',
        'cobrar_na_entrega' => 'boolean',
        'valido_no_intervalo' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'estacao_user', 'estacao_id', 'user_id')->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos';
    protected $fillable = [
        'nome',
        'ativo',
        'tipo_frota_id', // van, carro
        'grupo_frota_id', // frota
        'marca_id', // marca
        'modelo_id', // modelo
        'segmento_id', // segmento
        'status_veiculo_id', // status
        'ano_fabricacao',
        'ano_modelo',
        'placa_licenca',
        'chassi',
        'renavam',
        'cor',
        'data_placa_licenca',
        'data_compra',
        'data_validade',
        'kms',
        'limite_kms',
        'numero_motor',
        'vendido',
        'data_vendido',
        'proxima_revisao_kms',
        'proxima_manutencao_kms',
        'data_proxima_manutencao',
        'data_proxima_inspecao',
    ];
}

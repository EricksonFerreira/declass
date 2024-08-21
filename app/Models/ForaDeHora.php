<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForaDeHora extends Model
{
    protected $table = 'fora_de_hora';

    protected $fillable = [
        'estacao_id',
        'cobrar_na_coleta',
        'cobrar_na_entrega',
        'valido_no_intervalo'
    ];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoCalcao extends Model
{
    protected $table = 'configuracoes_calcao';

    const TIPO_FIXO = 'fixo';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Empresa;
use App\Models\PlanoMotoristaApp;
use App\Models\User;

class MotoristaAppSolicitacao extends Model
{
    use HasFactory;

    protected $table = 'motorista_app_solicitacoes';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'plano_motorista_app_id',
        'status',
        'dados_json',
        'documentos_json',
    ];

    protected $casts = [
        'dados_json' => 'array',
        'documentos_json' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function planoMotoristaApp()
    {
        return $this->belongsTo(PlanoMotoristaApp::class, 'plano_motorista_app_id');
    }
}

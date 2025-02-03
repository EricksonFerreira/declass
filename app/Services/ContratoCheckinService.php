<?php

namespace App\Services;

use App\Models\ContratoCheckin;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ContratoCheckinService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(ContratoCheckin $tipo_checklist)
    {
        $this->model = $tipo_checklist;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof ContratoCheckin) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\ContratoCheckin");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function assinarDocumento($id,$nomeArquivo)
    {
        try {
            $registro = $this->model->where('id', $id)->first();
            $registro->assinatura = $nomeArquivo;
            $registro->data_assinatura = date('Y-m-d H:i');
            $registro->assinado = 'S';
            $registro->save();

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function salvarArquivoAssinatura($base64assinatura){
         // Obtém a string base64 da assinatura
         $assinatura = $base64assinatura;
            
         // Remove o cabeçalho da string base64 (ex: "data:image/png;base64,")
         $assinatura = explode(',', $assinatura)[1];
         
         // Decodifica a string base64
         $imagemDecodificada = base64_decode($assinatura);
         
         // Gera um nome único para o arquivo
         $nomeArquivo = 'assinatura_' . time() . '.png';
         
         // Define o caminho onde a assinatura será salva
         $caminhoArquivo = storage_path('app/public/assinaturas/' . $nomeArquivo);
         
         // Cria o diretório se não existir
         if (!file_exists(dirname($caminhoArquivo))) {
             mkdir(dirname($caminhoArquivo), 0755, true);
         }

         // Salvar assinatura do contrato do ususario no banco
         
         // Salva o arquivo
         file_put_contents($caminhoArquivo, $imagemDecodificada);
    }
}

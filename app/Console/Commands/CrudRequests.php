<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrudRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:requests {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar os arquivos de Form Request para um recurso';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $ucFistName = ucfirst($name);
        $requests = ['Store', 'Update', 'Index'];

        foreach ($requests as $request) {
            $className = "$ucFistName\\{$request}{$name}Request";
            $this->call('make:request', ['name' => $className]);

            // Caminho do arquivo gerado
            $requestPath = app_path("Http/Requests/{$className}.php");
            if (file_exists($requestPath)) {
                $requestContent = file_get_contents($requestPath);
                // Substituir return false; por return true;
                $requestContent = str_replace('return false;', 'return true;', $requestContent);

                if ($request === 'Store') {
                    $requestContent = $this->getContentStore($requestContent);
                } elseif ($request === 'Update') {
                    $requestContent = $this->getContentUpdate($requestContent);
                }

                file_put_contents($requestPath, $requestContent);
            }
        }

        $this->info('Form Requests criados com sucesso!');
        return 0;
    }

    private function getContentStore($requestContent)
    {
        $rulesMethod = <<<'EOD'

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
         */
        public function rules(): array
        {
            return [
                'nome' => 'required|string|max:255',
                'ativo' => 'required|string|max:1',
            ];
        }

        public function messages()
        {
            return [
                'nome.required' => 'O campo nome é obrigatório',
                'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
                'ativo.required' => 'O campo ativo é obrigatório',
                'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
            ];
        }
    EOD;
        return $requestContent = str_replace(' /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }', $rulesMethod, $requestContent);
    }
    private function getContentUpdate($requestContent)
    {
        $rulesMethod = <<<'EOD'

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
         */
        public function rules(): array
        {
            return [
                'nome' => 'required|string|max:255|unique:tipo_transacoes,nome,' . $this->route('tipotransacao'),
                'ativo' => 'required|string|max:1',
            ];
        }

        public function messages()
        {
            return [
        'nome.required' => 'O campo nome é obrigatório.',
        'nome.string' => 'O campo nome deve ser uma string.',
        'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
        'nome.unique' => 'O nome informado já está em uso.',
        'ativo.required' => 'O campo ativo é obrigatório.',
        'ativo.string' => 'O campo ativo deve ser uma string.',
        'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
            ];
        }
    EOD;
    return  $requestContent = str_replace(' /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }', $rulesMethod, $requestContent);
    }
}

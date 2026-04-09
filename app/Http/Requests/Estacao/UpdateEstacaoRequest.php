<?php

namespace App\Http\Requests\Estacao;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                'unique:estacoes,nome,' . $this->route('estacao') . ',id'
            ],
            'email' => 'required|email|max:255',
            'telefone' => ['nullable', 'string', 'max:20', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'celular' => [
                'required',
                'string',
                'max:20',
                'regex:/^\(\d{2}\) \d{5}-\d{4}$/'
            ],
            'pais_id' => 'required|integer|exists:paises,id',
            'cidade' => 'required|string|max:255',
            'nfse_municipio_codigo' => 'nullable|string|max:10',
            'nfse_inscricao_municipal' => 'nullable|string|max:30',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'zip_code' => 'required|string|max:10',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'ativo' => 'required|string|max:1|in:S,N',
            'checkin' => 'required|string|max:1|in:S,N',
            'checkout' => 'required|string|max:1|in:S,N',
            'hora_abertura' => 'required|array',
            'hora_abertura.*' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_fechamento' => 'required|array',
            'hora_fechamento.*' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_inicio_almoco' => 'required|array',
            'hora_inicio_almoco.*' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_fim_almoco' => 'required|array',
            'hora_fim_almoco.*' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_inicio_fora_expediente' => 'required|array',
            'hora_inicio_fora_expediente.*' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_fim_fora_expediente' => 'required|array',
            'hora_fim_fora_expediente.*' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'cobrar_na_coleta' => 'required|string|max:1|in:S,N',
            'cobrar_na_entrega' => 'required|string|max:1|in:S,N',
            'valido_no_intervalo' => 'required|string|max:1|in:S,N',
            'diasUteis' => 'array|min:1',
            'diasUteis.*' => 'integer|exists:dias_uteis,id',
        ];
    }

    public function messages(): array
    {
        return [
            'telefone.regex' => 'Telefone invalido. Use o formato (00) 00000-0000.',
            'hora_abertura.*.required' => 'Preencha todos os campos de Horario Diario na aba Horarios.',
            'hora_fechamento.*.required' => 'Preencha todos os campos de Horario Diario na aba Horarios.',
            'hora_inicio_almoco.*.required' => 'Preencha todos os campos de Horario Diario na aba Horarios.',
            'hora_fim_almoco.*.required' => 'Preencha todos os campos de Horario Diario na aba Horarios.',
            'hora_inicio_fora_expediente.*.required' => 'Preencha todos os campos de Horario Diario na aba Horarios.',
            'hora_fim_fora_expediente.*.required' => 'Preencha todos os campos de Horario Diario na aba Horarios.',
            'hora_abertura.*.regex' => 'Informe horarios diarios validos no formato 24h.',
            'hora_fechamento.*.regex' => 'Informe horarios diarios validos no formato 24h.',
            'hora_inicio_almoco.*.regex' => 'Informe horarios diarios validos no formato 24h.',
            'hora_fim_almoco.*.regex' => 'Informe horarios diarios validos no formato 24h.',
            'hora_inicio_fora_expediente.*.regex' => 'Informe horarios diarios validos no formato 24h.',
            'hora_fim_fora_expediente.*.regex' => 'Informe horarios diarios validos no formato 24h.',
            'hora_abertura.*.not_in' => 'Preencha os horarios diarios na aba Horarios para salvar a estacao.',
            'hora_fechamento.*.not_in' => 'Preencha os horarios diarios na aba Horarios para salvar a estacao.',
            'hora_inicio_almoco.*.not_in' => 'Preencha os horarios diarios na aba Horarios para salvar a estacao.',
            'hora_fim_almoco.*.not_in' => 'Preencha os horarios diarios na aba Horarios para salvar a estacao.',
            'hora_inicio_fora_expediente.*.not_in' => 'Preencha os horarios diarios na aba Horarios para salvar a estacao.',
            'hora_fim_fora_expediente.*.not_in' => 'Preencha os horarios diarios na aba Horarios para salvar a estacao.',
            'diasUteis.min' => 'Selecione ao menos um dia trabalhado.',
            'diasUteis.*.exists' => 'Um dos dias trabalhados selecionados e invalido.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $aberturas = $this->input('hora_abertura', []);
            $fechamentos = $this->input('hora_fechamento', []);
            $inicioAlmocos = $this->input('hora_inicio_almoco', []);
            $fimAlmocos = $this->input('hora_fim_almoco', []);
            $funciona24h = $this->input('funciona_24h', []);

            $toMinutes = function ($value): ?int {
                if (!is_string($value) || trim($value) === '' || $value === '00:00') {
                    return null;
                }

                [$h, $m] = array_pad(explode(':', $value), 2, null);
                if ($h === null || $m === null || !is_numeric($h) || !is_numeric($m)) {
                    return null;
                }

                return ((int) $h * 60) + (int) $m;
            };

            $total = max(count($aberturas), count($fechamentos), count($inicioAlmocos), count($fimAlmocos), count($funciona24h));

            for ($i = 0; $i < $total; $i++) {
                $abertura = $toMinutes($aberturas[$i] ?? null);
                $fechamento = $toMinutes($fechamentos[$i] ?? null);
                $inicioAlmoco = $toMinutes($inicioAlmocos[$i] ?? null);
                $fimAlmoco = $toMinutes($fimAlmocos[$i] ?? null);

                $is24h = isset($funciona24h[$i]) && $funciona24h[$i] === 'S';

                if ($is24h) {
                    continue;
                }

                if ($abertura === null || $fechamento === null) {
                    $validator->errors()->add("hora_abertura.$i", 'Preencha todos os campos de Horario Diario na aba Horarios.');
                    $validator->errors()->add("hora_fechamento.$i", 'Preencha todos os campos de Horario Diario na aba Horarios.');
                    continue;
                }

                if ($inicioAlmoco !== null && ($inicioAlmoco < $abertura || $inicioAlmoco > $fechamento)) {
                    $validator->errors()->add("hora_inicio_almoco.$i", 'Horario de almoco deve estar dentro do horario de abertura e fechamento.');
                }

                if ($fimAlmoco !== null && ($fimAlmoco < $abertura || $fimAlmoco > $fechamento)) {
                    $validator->errors()->add("hora_fim_almoco.$i", 'Horario de almoco deve estar dentro do horario de abertura e fechamento.');
                }

                if ($inicioAlmoco !== null && $fimAlmoco !== null && $inicioAlmoco >= $fimAlmoco) {
                    $validator->errors()->add("hora_fim_almoco.$i", 'Fim do almoco deve ser maior que o inicio do almoco.');
                }
            }
        });
    }
}

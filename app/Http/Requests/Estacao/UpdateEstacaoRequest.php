<?php

namespace App\Http\Requests\Estacao;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            'telefone' => 'required|string|max:20',
            'celular' => [
                'required',
                'string',
                'max:20',
                'regex:/^\(\d{2}\) \d{5}-\d{4}$/'
            ],
            'pais_id' => 'required|integer|exists:paises,id',
            'cidade' => 'required|string|max:255',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'zip_code' => 'required|string|max:10',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'ativo' => 'required|string|max:1|in:S,N',
            'checkin' => 'required|string|max:1|in:S,N',
            'checkout' => 'required|string|max:1|in:S,N',
            'hora_abertura' => 'required|array',
            'hora_abertura.*' =>  ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_fechamento' => 'required|array',
            'hora_fechamento.*' =>  ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_inicio_almoco' => 'required|array',
            'hora_inicio_almoco.*' =>  ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_fim_almoco' => 'required|array',
            'hora_fim_almoco.*' =>  ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_inicio_fora_expediente' => 'required|array',
            'hora_inicio_fora_expediente.*' =>  ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'hora_fim_fora_expediente' => 'required|array',
            'hora_fim_fora_expediente.*' =>  ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'cobrar_na_coleta' => 'required|string|max:1|in:S,N',
            'cobrar_na_entrega' => 'required|string|max:1|in:S,N',
            'valido_no_intervalo' => 'required|string|max:1|in:S,N',
            'diasUteis' => 'array|min:1',
            'diasUteis.*' => 'integer|in:1,2,3,4,5,6,7',
        ];
    }

    public function messages()
    {
        return [
            'diasUteis.*.in' => 'O campo dias úteis deve ser um dos seguintes valores: 1, 2, 3, 4, 5, 6, 7',
            'diasUteis.*.integer' => 'O campo dias úteis deve ser um número inteiro',
            'diasUteis.min' => 'O campo dias úteis deve ter pelo menos um valor',
            'nome.required' => 'O campo nome é obrigatório',
            'nome.string' => 'O campo nome deve ser uma string',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'nome.unique' => 'O nome informado já está em uso',
            'email.required' => 'O campo email é obrigatório',
            'email.email' => 'O campo email deve ser um endereço de e-mail válido',
            'email.max' => 'O campo email deve ter no máximo 255 caracteres',
            'telefone.required' => 'O campo telefone é obrigatório',
            'telefone.string' => 'O campo telefone deve ser uma string',
            'telefone.max' => 'O campo telefone deve ter no máximo 20 caracteres',
            'celular.required' => 'O campo celular é obrigatório',
            'celular.string' => 'O campo celular deve ser uma string',
            'celular.max' => 'O campo celular deve ter no máximo 20 caracteres',
            'celular.regex' => 'O campo celular deve estar no formato (99) 99999-9999',
            'pais_id.required' => 'O campo país é obrigatório',
            'pais_id.integer' => 'O campo país deve ser um número inteiro',
            'pais_id.exists' => 'O país informado não existe',
            'cidade.required' => 'O campo cidade é obrigatório',
            'cidade.string' => 'O campo cidade deve ser uma string',
            'cidade.max' => 'O campo cidade deve ter no máximo 255 caracteres',
            'endereco.required' => 'O campo endereço é obrigatório',
            'endereco.string' => 'O campo endereço deve ser uma string',
            'endereco.max' => 'O campo endereço deve ter no máximo 255 caracteres',
            'numero.required' => 'O campo número é obrigatório',
            'numero.string' => 'O campo número deve ser uma string',
            'numero.max' => 'O campo número deve ter no máximo 10 caracteres',
            'zip_code.required' => 'O campo CEP é obrigatório',
            'zip_code.string' => 'O campo CEP deve ser uma string',
            'zip_code.max' => 'O campo CEP deve ter no máximo 10 caracteres',
            'latitude.required' => 'O campo latitude é obrigatório',
            'latitude.string' => 'O campo latitude deve ser uma string',
            'latitude.max' => 'O campo latitude deve ter no máximo 255 caracteres',
            'longitude.required' => 'O campo longitude é obrigatório',
            'longitude.string' => 'O campo longitude deve ser uma string',
            'longitude.max' => 'O campo longitude deve ter no máximo 255 caracteres',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.string' => 'O campo ativo deve ser uma string',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
            'ativo.in' => 'O campo ativo deve ser um dos seguintes valores: Permite ou Não Permite',
            'checkin.required' => 'O campo checkin é obrigatório',
            'checkin.string' => 'O campo checkin deve ser uma string',
            'checkin.max' => 'O campo checkin deve ter no máximo 1 caractere',
            'checkin.in' => 'O campo checkin deve ser um dos seguintes valores: Permite ou Não Permite',
            'checkout.required' => 'O campo checkout é obrigatório',
            'checkout.string' => 'O campo checkout deve ser uma string',
            'checkout.max' => 'O campo checkout deve ter no máximo 1 caractere',
            'checkout.in' => 'O campo checkout deve ser um dos seguintes valores: Permite ou Não Permite',
            'hora_abertura.required' => 'O campo hora de abertura é obrigatório',
            'hora_abertura.array' => 'O campo hora de abertura deve ser um array',
            'hora_abertura.*.required' => 'O campo hora de abertura é obrigatório',
            'hora_abertura.*.date_format' => 'O campo hora de abertura deve ser uma data válida',
            'hora_fechamento.required' => 'O campo hora de fechamento é obrigatório',
            'hora_fechamento.array' => 'O campo hora de fechamento deve ser um array',
            'hora_fechamento.*.required' => 'O campo hora de fechamento é obrigatório',
            'hora_fechamento.*.date_format' => 'O campo hora de fechamento deve ser uma data válida',
            'hora_inicio_almoco.required' => 'O campo hora de início do almoço é obrigatório',
            'hora_inicio_almoco.array' => 'O campo hora de início do almoço deve ser um array',
            'hora_inicio_almoco.*.required' => 'O campo hora de início do almoço é obrigatório',
            'hora_inicio_almoco.*.date_format' => 'O campo hora de início do almoço deve ser uma data válida',
            'hora_fim_almoco.required' => 'O campo hora de fim do almoço é obrigatório',
            'hora_fim_almoco.array' => 'O campo hora de fim do almoço deve ser um array',
            'hora_fim_almoco.*.required' => 'O campo hora de fim do almoço é obrigatório',
            'hora_fim_almoco.*.date_format' => 'O campo hora de fim do almoço deve ser uma data válida',
            'hora_inicio_fora_expediente.required' => 'O campo hora de início fora do expediente é obrigatório',
            'hora_inicio_fora_expediente.array' => 'O campo hora de início fora do expediente deve ser um array',
            'hora_inicio_fora_expediente.*.required' => 'O campo hora de início fora do expediente é obrigatório',
            'hora_inicio_fora_expediente.*.date_format' => 'O campo hora de início fora do expediente deve ser uma data válida',
            'hora_fim_fora_expediente.required' => 'O campo hora de fim fora do expediente é obrigatório',
            'hora_fim_fora_expediente.array' => 'O campo hora de fim fora do expediente deve ser um array',
            'hora_fim_fora_expediente.*.required' => 'O campo hora de fim fora do expediente é obrigatório',
            'hora_fim_fora_expediente.*.date_format' => 'O campo hora de fim fora do expediente deve ser uma data válida',
            'cobrar_na_coleta.required' => 'O campo cobrar na coleta é obrigatório',
            'cobrar_na_coleta.string' => 'O campo cobrar na coleta deve ser uma string',
            'cobrar_na_coleta.max' => 'O campo cobrar na coleta deve ter no máximo 1 caractere',
            'cobrar_na_coleta.in' => 'O campo cobrar na coleta deve ser um dos seguintes valores: Permite ou Não Permite',
            'cobrar_na_entrega.required' => 'O campo cobrar na entrega é obrigatório',
            'cobrar_na_entrega.string' => 'O campo cobrar na entrega deve ser uma string',
            'cobrar_na_entrega.max' => 'O campo cobrar na entrega deve ter no máximo 1 caractere',
            'cobrar_na_entrega.in' => 'O campo cobrar na entrega deve ser um dos seguintes valores: Permite ou Não Permite',
            'valido_no_intervalo.required' => 'O campo válido no intervalo é obrigatório',
            'valido_no_intervalo.string' => 'O campo válido no intervalo deve ser uma string',
            'valido_no_intervalo.max' => 'O campo válido no intervalo deve ter no máximo 1 caractere',
            'valido_no_intervalo.in' => 'O campo válido no intervalo deve ser um dos seguintes valores: Permite ou Não Permite',
        ];
    }
}

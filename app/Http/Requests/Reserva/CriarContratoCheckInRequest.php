<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use App\Services\Reserva\ReservaResolver;

class CriarContratoCheckInRequest extends FormRequest
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
        // Verifica se já existe contrato para esta reserva (modo edição / motorista app)
        $contratoJaExiste = false;
        try {
            $codigo = (string) ($this->route('codigo') ?? $this->route('id') ?? $this->input('reserva_id'));
            if ($codigo !== '') {
                /** @var ReservaResolver $resolver */
                $resolver = app(ReservaResolver::class);
                $reservaResolvida = $resolver->resolver($codigo);

                if ($reservaResolvida->ehMotoristaApp()) {
                    $registro = $reservaResolvida->registro;
                    $contratoId = (int) data_get($registro->documentos_json, 'checkin.contrato_checkin_id', 0);
                    $reservaOpId = (int) data_get($registro->documentos_json, 'checkin.reserva_operacional_id', 0);

                    $contratoJaExiste = ($contratoId > 0 && DB::table('contrato_checkin')->where('id', $contratoId)->exists())
                        || ($reservaOpId > 0 && DB::table('contrato_checkin')->where('reserva_id', $reservaOpId)->exists())
                        || DB::table('contrato_checkin')->where('reserva_id', (int) $registro->id)->exists();
                } else {
                    $reservaId = (int) $reservaResolvida->registro->id;
                    $contratoJaExiste = DB::table('contrato_checkin')->where('reserva_id', $reservaId)->exists();
                }
            }
        } catch (\Throwable $e) {
            $contratoJaExiste = false;
        }

        $fotoRule = $contratoJaExiste ? 'nullable|image|max:8192' : 'required|image|max:8192';

        return [
            'data_saida' => 'required|date',
            'hora_saida' => 'required|date_format:H:i',
            'marca_id' => 'nullable|integer|exists:marcas,id',
            'modelo_id' => 'nullable|integer|exists:modelos,id',
            'nivel_combustivel' => 'required|string|in:Reserva,1/8,1/4,3/8,1/2,5/8,3/4,7/8,Cheio',
            'notas_saida' => 'nullable|string|max:255',
            'observacoes_saida' => 'nullable|string|max:255',
            'quilometragem_atual' => 'required|numeric',
            'veiculo_id' => 'required|integer|exists:veiculos,id',
            'motivo_upgrade' => 'nullable|string|max:500',
            'foto_direita' => $fotoRule,
            'foto_esquerda' => $fotoRule,
            'foto_frente' => $fotoRule,
            'foto_tras' => $fotoRule,
            'foto_velocimetro' => $fotoRule,
            'foto_combustivel' => $fotoRule,
            'danos' => 'nullable',
            'dano' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'data_saida.required' => 'A data de saída é obrigatória.',
            'data_saida.date' => 'A data de saída deve ser uma data válida.',
            'hora_saida.required' => 'A hora de saída é obrigatória.',
            'hora_saida.date_format' => 'A hora de saída deve ser uma hora válida.',
            'marca_id.integer' => 'A marca deve ser um número inteiro.',
            'marca_id.exists' => 'A marca selecionada é inválida.',
            'modelo_id.integer' => 'O modelo deve ser um número inteiro.',
            'modelo_id.exists' => 'O modelo selecionado é inválido.',
            'nivel_combustivel.required' => 'O nível de combustível é obrigatório.',
            'nivel_combustivel.string' => 'O nível de combustível deve ser uma string.',
            'nivel_combustivel.in' => 'O nível de combustível selecionado é inválido.',
            'notas_saida.string' => 'As notas de saída devem ser uma string.',
            'notas_saida.max' => 'As notas de saída não podem ter mais de 255 caracteres.',
            'observacoes_saida.string' => 'As observações de saída devem ser uma string.',
            'observacoes_saida.max' => 'As observações de saída não podem ter mais de 255 caracteres.',
            'quilometragem_atual.required' => 'A quilometragem atual é obrigatória.',
            'quilometragem_atual.numeric' => 'A quilometragem atual deve ser um número.',
            'veiculo_id.required' => 'Você não selecionou um veículo.',
            'veiculo_id.integer' => 'O veículo deve ser um número inteiro.',
            'veiculo_id.exists' => 'O veículo não existe.',
        ];
    }
}

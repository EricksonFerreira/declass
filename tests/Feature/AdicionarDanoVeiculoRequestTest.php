<?php

use App\Http\Requests\Reserva\AdicionarDanoVeiculoRequest;

describe('AdicionarDanoVeiculoRequest', function () {
    it('converte valores de placeholder em dano ausente para invalidar a validação', function () {
        $request = new AdicionarDanoVeiculoRequest();
        $request->merge([
            'dano_id' => 'Selecione um dano',
        ]);

        $request->prepareForValidation();

        expect($request->input('dano_id'))->toBeNull();
    });

    it('preserva ids numéricos válidos', function () {
        $request = new AdicionarDanoVeiculoRequest();
        $request->merge([
            'dano_id' => '42',
        ]);

        $request->prepareForValidation();

        expect($request->input('dano_id'))->toBe(42);
    });
});

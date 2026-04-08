<?php

return [
    // Horarios diários para executar a replicacao no formato HH:MM, separados por virgula na env
    // Exemplo: REPLICACAO_HORARIOS="02:00,10:00,18:00"
    'horarios_diarios' => array_filter(array_map('trim', explode(',', env('REPLICACAO_HORARIOS', '02:00')))),

    // Opcional: empresa origem padrao (slug) para replicacao automatica. Se nulo, o comando precisa receber parametros.
    'empresa_origem_id' => env('REPLICACAO_EMPRESA_ORIGEM_ID', null),

    // Opcional: empresas destino padrao (comma separated ids)
    'empresas_destino_ids' => array_filter(array_map('intval', array_filter(array_map('trim', explode(',', env('REPLICACAO_EMPRESAS_DESTINO', '')))))),

    // Opcional: modulos padrao (comma separated keys matching modulosDisponiveis keys)
    'modulos' => array_filter(array_map('trim', explode(',', env('REPLICACAO_MODULOS', '')))),
];

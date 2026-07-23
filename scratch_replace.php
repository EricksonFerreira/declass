<?php

$content = file_get_contents('config/adminlte.php');

// Replace standard Listagem/Novo pairs
$pattern = "/'submenu'\s*=>\s*\[\s*\[\s*'text'\s*=>\s*'Listagem(?:[^']*)',\s*(?:'route'\s*=>\s*'([^']+)'|'url'\s*=>\s*'([^']+)')\s*,\s*'icon'\s*=>\s*'[^']+',\s*\],\s*\[\s*'text'\s*=>\s*'Nov[oa](?:[^']*)',\s*'route'\s*=>\s*'[^']+',\s*'icon'\s*=>\s*'[^']+',\s*\],\s*\]/";

$new_content = preg_replace_callback($pattern, function($matches) {
    $routeOrUrl = !empty($matches[1]) ? "'route' => '{$matches[1]}'" : "'url' => '{$matches[2]}'";
    return $routeOrUrl;
}, $content);

// For reservas_atendimento
$new_content = str_replace(
"        [
            'text'    => 'sidebar.reservas_atendimento',
            'icon'    => 'fas fa-calendar-check',
            'submenu' => [
                [
                    'text' => 'sidebar.reserva',
                    'url'  => 'reserva',
                    'icon' => 'fas fa-list',
                    'can'  => 'reserva.index',
                ],
                [
                    'text' => 'sidebar.nova_reserva',
                    'route' => 'reserva.create',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'reserva.create',
                ],
                [
                    'text' => 'sidebar.minhas_reservas',
                    'url'  => 'reserva/minhas',
                    'icon' => 'fas fa-bookmark',
                    'can'  => 'reserva.minhas',
                ],
            ],
        ],", 
"        [
            'text'  => 'sidebar.reservas_atendimento',
            'url'   => 'reserva',
            'icon'  => 'fas fa-calendar-check',
            'can'   => 'reserva.index',
        ],
        [
            'text'  => 'sidebar.minhas_reservas',
            'url'   => 'reserva/minhas',
            'icon'  => 'fas fa-bookmark',
            'can'   => 'reserva.minhas',
        ],", $new_content);

// For sidebar.veiculo
$new_content = str_replace(
"        [
            'text'    => 'sidebar.veiculo',
            'icon'    => 'fas fa-car',
            'submenu' => [
                [
                    'text' => 'Listagem de Veículos',
                    'route' => 'veiculo.index',
                    'icon' => 'fas fa-car-side',
                ],
                [
                    'text' => 'Novo Veículo',
                    'route' => 'veiculo.create',
                    'icon' => 'fas fa-plus-circle',
                ],
                [
                    'text' => 'Disponibilidade',
                    'route' => 'veiculo.disponibilidade',
                    'icon' => 'fas fa-calendar-alt',
                ],
            ],
        ],",
"        [
            'text'  => 'sidebar.veiculo',
            'route' => 'veiculo.index',
            'icon'  => 'fas fa-car',
        ],
        [
            'text'  => 'Disponibilidade',
            'route' => 'veiculo.disponibilidade',
            'icon'  => 'fas fa-calendar-alt',
        ],", $new_content);

file_put_contents('config/adminlte.php', $new_content);
echo "Done.\n";

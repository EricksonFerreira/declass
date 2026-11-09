<?php

namespace App\Listeners;

use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

/**
 * Menu lateral do professor: apenas a rotina pedagogica.
 * Remove os grupos administrativos globais e adiciona os grupos
 * operacionais proprios (Calendario, Comunicacao e Biblioteca).
 */
class ProfessorMenu
{
    /**
     * Chaves dos grupos globais que nao fazem parte do menu do professor.
     *
     * @var string[]
     */
    private const GRUPOS_REMOVIDOS = [
        'org_admin',
        'op_pedagogica',
        'relatorios',
        'comunicacao',
        'operacoes',
        'financeiro',
        'configuracoes',
    ];

    public function handle(BuildingMenu $event): void
    {
        if (! auth()->user()?->isProfessor()) {
            return;
        }

        foreach (self::GRUPOS_REMOVIDOS as $key) {
            $event->menu->remove($key);
        }

        $event->menu->add(
            [
                'text' => 'Calendário',
                'icon' => 'fas fa-calendar-check',
                'submenu' => [
                    [
                        'text' => 'Calendário Letivo',
                        'route' => 'calendario.index',
                        'can' => 'calendario.index',
                        'icon' => 'fas fa-calendar-alt',
                    ],
                    [
                        'text' => 'Feriados',
                        'route' => 'feriado.index',
                        'can' => 'feriado.index',
                        'icon' => 'fas fa-umbrella-beach',
                    ],
                ],
            ],
            [
                'text' => 'Comunicação',
                'icon' => 'fas fa-bullhorn',
                'submenu' => [
                    [
                        'text' => 'Agenda',
                        'route' => 'evento.index',
                        'can' => 'evento.index',
                        'icon' => 'fas fa-calendar-alt',
                    ],
                    [
                        'text' => 'Avisos',
                        'route' => 'aviso.index',
                        'can' => 'aviso.index',
                        'icon' => 'fas fa-bullhorn',
                    ],
                    [
                        'text' => 'Pesquisas',
                        'route' => 'pesquisa.index',
                        'can' => 'pesquisa.index',
                        'icon' => 'fas fa-clipboard-question',
                    ],
                ],
            ],
            [
                'text' => 'Biblioteca',
                'icon' => 'fas fa-book-open',
                'submenu' => [
                    [
                        'text' => 'Meus Empréstimos',
                        'route' => 'portal.emprestimos',
                        'can' => 'portal.emprestimos',
                        'icon' => 'fas fa-book-reader',
                    ],
                ],
            ],
        );
    }
}

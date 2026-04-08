<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            ['nome' => 'Pendente',   'descricao' => 'Reserva criada, aguardando confirmação', 'cor' => '#f59e0b'],
            ['nome' => 'Confirmada', 'descricao' => 'Reserva confirmada pela estação',          'cor' => '#3b82f6'],
            ['nome' => 'Check-in',   'descricao' => 'Veículo entregue ao cliente',              'cor' => '#8b5cf6'],
            ['nome' => 'Check-out',  'descricao' => 'Veículo devolvido',                        'cor' => '#10b981'],
            ['nome' => 'Cancelada',  'descricao' => 'Reserva cancelada',                        'cor' => '#ef4444'],
            ['nome' => 'No-show',    'descricao' => 'Cliente não compareceu',                   'cor' => '#6b7280'],
        ];

        foreach ($statuses as $s) {
            $existe = DB::table('status_reserva')
                ->whereRaw('LOWER(nome) = ?', [strtolower($s['nome'])])
                ->exists();
            if (!$existe) {
                DB::table('status_reserva')->insert([
                    'nome'       => $s['nome'],
                    'descricao'  => $s['descricao'],
                    'ativo'      => 'S',
                    'cor'        => $s['cor'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void {}
};

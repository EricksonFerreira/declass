<?php

namespace App\Actions\Clockify\Report;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StoreMembroAction
{
    /**
     * Create a new user (member).
     */
    public function handle(array $data): User
    {
        // Map name to nome
        if (isset($data['name']) && ! isset($data['nome'])) {
            $data['nome'] = $data['name'];
        }

        // Set default company
        if (! isset($data['empresa_id'])) {
            $data['empresa_id'] = auth()->user()?->empresa_id;
        }

        // Assign the company default member role
        if (! isset($data['role_id'])) {
            $data['role_id'] = \App\Models\Regra::where('guard_name', 'web')
                ->where('empresa_id', $data['empresa_id'])
                ->where('name', 'usuario')
                ->value('id');
        }

        // Ensure password is hashed
        $data['password'] = Hash::make($data['password']);

        // Force password change on first login
        $data['forcar_troca_senha'] = true;

        return User::create($data);
    }
}

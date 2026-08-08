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

        // Assign default collaborator role
        if (! isset($data['role_id'])) {
            $colaboradorRole = \App\Models\Regra::where('name', 'colaborador_administrativo')
                ->orWhere('name', 'Colaborador Administrativo')
                ->first();
            if ($colaboradorRole) {
                $data['role_id'] = $colaboradorRole->id;
            } else {
                $data['role_id'] = 8; // Fallback
            }
        }

        // Ensure password is hashed
        $data['password'] = Hash::make($data['password']);

        // Force password change on first login
        $data['forcar_troca_senha'] = true;

        return User::create($data);
    }
}

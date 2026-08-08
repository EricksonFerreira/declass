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
        // Ensure password is hashed
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }
}

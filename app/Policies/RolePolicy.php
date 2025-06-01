<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role)
    {
        return $user->isAdmin();
    }
}

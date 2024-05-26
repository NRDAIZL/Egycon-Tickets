<?php

namespace App\Helpers;

use App\Models\User;

class RoleHelper {

    private $role;

    public function __construct(string $roles = "admin|organizer|volunteer") {
        $this->role = $roles;
    }

    function checkUserPermission(User $user) : bool {
        $roles = explode("|", $this->role);
        $has_permission = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $has_permission = true;
            }
        }
        return $has_permission;
    }

    
}
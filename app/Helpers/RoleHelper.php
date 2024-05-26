<?php

namespace App\Helpers;

use App\Models\User;

class RoleHelper {

    private $admin_roles = "admin|organizer|volunteer";
    private $role;

    public function __construct(string $roles = $this->admin_roles) {
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
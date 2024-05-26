<?php

namespace App\Helpers;

use App\Models\User;

class RoleHelper {

    private $event_id;
    private $role;

    public function __construct(string $roles = "admin|organizer|volunteer", int $event_id) {
        $this->role = $roles;
        $this->event_id = $event_id;
        setPermissionsTeamId($this->event_id);
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
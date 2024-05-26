<?php

namespace App\Helpers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationsHelper {
    
    private $notification;

    public function __construct(Notification $notification) {
        $this->notification = $notification;
    }

    function sendToEventAdmins($event_id) {
        $users = Event::find($event_id)->users()->get();
        foreach($users as $user){
            if((new RoleHelper("admin|organizer"))->checkUserPermission($user)){
                $user->notify($this->notification);
            }
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvitation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;

class UserController extends Controller
{
    public function invite($event_id){
        $event = auth()->user()->events()->where('event_id', $event_id)->first();
        if(!$event){
            return redirect()->back()->with('error', 'You are not authorized to invite users to this event');
        }
        $roles = Role::all();
        return view('admin.users.invite', compact('event','roles'));
    }

    public function invite_post(Request $request, $event_id){
        $event = auth()->user()->events()->where('event_id', $event_id)->first();
        if(!$event){
            return redirect()->back()->with('error', 'You are not authorized to invite users to this event');
        }
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|exists:roles,id'
        ]);
        // check if user already exists in the event
        $user = User::where('email', $request->email)->first();
        if (strtolower(auth()->user()->email) == strtolower($request->email)) {
            return redirect()->back()->with('error', 'You cannot invite yourself to an event');
        }
        if($user){
            if($event->users()->where('user_id', $user->id)->first()){
                return redirect()->back()->with('error', 'User already exists in this event');
            }
            $event->users()->attach($user->id);
            $user->assignRole($request->role);

            return redirect()->back()->with('success', 'User added to event successfully');
        }
        
        $invitation = $event->invitations()->create([
            'email' => $request->email,
            'role_id' => $request->role,
            'invited_by' => auth()->user()->id,
            'token' =>  Str::random(32),
            'expires_at' => now()->addDays(7),
            'event_id' => $event->id
        ]);

        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            $data = [
                "name"=> $request->name,
                "invite_sender_name"=> auth()->user()->name,
                "event_name"=> $event->name,
                "action_url"=> route('accept_invitation', $invitation->token),
            ];
            $template_id = 30325968;
            $sendResult = $client->sendEmailWithTemplate(
                "egycon@gamerslegacy.net",
                $request->email,
                $template_id,
                $data
            );
        } catch (PostmarkException $ex) {
            dd($ex);
            // If the client is able to communicate with the API in a timely fashion,
            // but the message data is invalid, or there's a server error,
            // a PostmarkException can be thrown.
            echo $ex->httpStatusCode;
            echo $ex->message;
            echo $ex->postmarkApiErrorCode;
        } catch (Exception $generalException) {
            dd($generalException);
            // A general exception is thrown if the API
            // was unreachable or times out.
        }

        return redirect()->back()->with('success', 'User invited successfully');
    }

    public function accept_invitation($token){
        $invitation = UserInvitation::where('token', $token)->first();
        if(!$invitation){
            return redirect()->route('login')->with('error', 'Invalid invitation token');
        }
        if($invitation->accepted_at){
            return redirect()->route('login')->with('error', 'Invitation already accepted');
        }
        if($invitation->expires_at < now()){
            return redirect()->route('login')->with('error', 'Invitation expired');
        }
        return view('admin.users.accept_invitation', compact('invitation'));
    }

    public function accept_invitation_post(Request $request, $token){
        $invitation = UserInvitation::where('token', $token)->first();
        if(!$invitation){
            return redirect()->route('login')->with('error', 'Invalid invitation token');
        }
        if($invitation->accepted_at){
            return redirect()->route('login')->with('error', 'Invitation already accepted');
        }
        if($invitation->expires_at < now()){
            return redirect()->route('login')->with('error', 'Invitation expired');
        }
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);
        // check if user already exists
        $user = User::where('email', $request->email)->first();
        if($user){
            if($user->events()->where('event_id', $invitation->event_id)->first()){
                return redirect()->route('login')->with('error', 'You are already registered to this event');
            }
        }else{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
        }
        
        $event = $invitation->event()->first();
        $event->users()->attach($user->id);
        setPermissionsTeamId($event->id);
        $user->assignRole($invitation->role_id);
        $invitation->update([
            'accepted_at' => now()
        ]);
        Auth::login($user);
        return redirect()->route('home');
    }

    public function view($event_id){
        $event = auth()->user()->events()->where('event_id', $event_id)->first();
        if(!$event){
            return redirect()->back()->with('error', 'You are not authorized to view users in this event');
        }
        // group users and invitations
        $users = $event->users()->get();
        setPermissionsTeamId($event->id);
        
        // get invitations that expiration date is not passed by 7 days
        $invitations = $event->invitations()->where('accepted_at', null)->where('expires_at', '>', now()->subDays(7))->get();
        return view('admin.users.view', compact('event','users','invitations'));
    }

    public function update($event_id, $user_id){
        $event = auth()->user()->events()->where('event_id', $event_id)->first();
        if(!$event){
            return redirect()->back()->with('error', 'You are not authorized to update users in this event');
        }
        $user = $event->users()->where('user_id', $user_id)->first();
        if(!$user){
            return redirect()->back()->with('error', 'User not found');
        }
        $roles = Role::all();
        $self_user = auth()->user()->id == $user->id;
        return view('admin.users.edit', compact('event', 'user', 'roles', 'self_user'));
    }

    public function update_post(Request $request, $event_id, $user_id){
        $event = auth()->user()->events()->where('event_id', $event_id)->first();
        if(!$event){
            return redirect()->back()->with('error', 'You are not authorized to update users in this event');
        }
        $user = $event->users()->where('user_id', $user_id)->first();
        if(!$user){
            return redirect()->back()->with('error', 'User not found');
        }
        $updated = [];

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|exists:roles,id'
        ]);

        if($user->email != $request->email){
            $invitation = $event->invitations()->where('email', $request->email)->first();
            if($invitation){
                return redirect()->back()->with('error', 'Email already exists in this event');
            }
            $updated[] = ['Email' => $request->email];
        }

        if($request->has('password') && strlen($request->password) > 0){
            $request->validate([
                'password' => 'required|min:6|confirmed'
            ]);
            $user->update([
                'password' => bcrypt($request->password)
            ]);
            $updated[] = ['Password' => str_repeat("*", strlen($request->password))];
        }

        if($request->name != $user->name){
            $updated[] = ['Name' => $request->name];
        }

        if($request->role != ($user->roles()->first()->id ?? null)){
            if($user->id == auth()->user()->id){
                return redirect()->back()->with('error', 'You cannot change your role');
            }
            $updated[] = ['Role' => Role::find($request->role)->name];    
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);


        $user->syncRoles([$request->role]);
        $updated_message = 'Nothing updated!';
        if(count($updated)){
            $updated_message = "User updated successfully. The following fields were updated: \n";
            foreach($updated as $field){
                $updated_message .= key($field) . ": ". $field[key($field)] . "\n";
            }
        }
        return redirect()->back()->with('success', nl2br($updated_message));
    }

    public function viewSettings(){
        /** @var User $user */
        $user = auth()->user();
        $telegramNotificationsEnabled = $user->getTelegramChatId() ? true : false;
        $telegramCode = $user->getTelegramCode();
        $telegramCodeQR = $user->getTelegramCodeQR();
        return view('admin.settings.all', compact('user', 'telegramNotificationsEnabled', 'telegramCode', 'telegramCodeQR'));
    }

    public function disableTelegramNotifications(){
        /** @var User $user */
        $user = auth()->user();
        $telegramChat = $user->getTelegramChatIdObject();
        if($telegramChat){
            $telegramChat->delete();
        }
        return redirect()->back()->with('success', 'Telegram notifications disabled');
    }

    public function viewEditProfile(){
        $user = auth()->user();
        return view('admin.settings.profile', compact('user'));
    }
}

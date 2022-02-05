<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
class SignUpController extends Controller
{
    public function index()
    {
        $committees = Committee::all();
        return view('signup',['committees'=>$committees]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:6|max:32',
            'nickname' => 'nullable|string|min:3|max:16',
            'email' => 'required|string|email|max:255|unique:users',
            'gender' => 'required|integer',
            'birth_date' => 'required|date',
            'phone' => 'required|numeric',
            'password' => 'required|min:6|confirmed',
            'committee' => 'required|exists:committees,id',
        ]);

        $user = User::create([
            'name' => $request['name'],
            'nickname' => $request['nickname'],
            'committee_id' => $request['committee'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'date_of_birth' => $request['birth_date'],
            'gender' => $request['gender'],
            'phone' => $request['phone'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        event(new Registered($user));
        
        return redirect()->route('home');
    }
    


    public function first_step(Request $request){
        $request->validate([
            'name' => 'required|string|min:6|max:60',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $data = [
            "name" => $request['name'],
            "email" => $request['email'],
        ];

        return $this->second_step($data);
    }



    public function second_step($data = [])
    {
        
        return view('pages.signup_second_step', $data);
    }

}

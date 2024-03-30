<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class DashboardController extends Controller
{
    public function index($event_id){
        return view('admin.home');
    }
}

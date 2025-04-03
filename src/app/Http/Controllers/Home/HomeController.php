<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->user_type === 'common') {
            return view('home.common', ['user' => $user]);
        } else if ($user->user_type === 'merchant') {
            return view('home.merchant', ['user' => $user]);
        }

        return redirect()->route('login.show');
    }
}

<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display the home page based on user type.
     *
     * This method checks the authenticated user's type and redirects them
     * to the appropriate view based on whether the user is 'common' or 'merchant'.
     * If the user type is unknown, they are redirected to the login page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user || !isset($user->user_type)) {
            return redirect()->route('login.show');
        }

        if ($user->user_type === 'common') {
            return view('home.common', ['user' => $user]);
        } else if ($user->user_type === 'merchant') {
            return view('home.merchant', ['user' => $user]);
        }

        return redirect()->route('login.show');
    }
}

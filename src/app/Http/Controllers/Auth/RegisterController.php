<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a new user registration.
     *
     * Validates the registration form data, creates a new user, and redirects
     * the user to the login page upon successful registration.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:5',
            'user_type' => 'required|in:common,merchant',
            'cpf' => 'nullable|required_if:user_type,common|min:11|max:14',
            'cnpj' => 'nullable|required_if:user_type,merchant|min:14|max:18',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'cpf' => $request->user_type === 'common' ? $request->cpf : null,
            'cnpj' => $request->user_type === 'merchant' ? $request->cnpj : null,
        ]);

        return redirect()->route('login');
    }
}

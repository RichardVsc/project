<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Transfer\TransferController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home.show');
})->name('');

Route::get('/home', [HomeController::class, 'index'])->name('home.show');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.show');
Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::post('/logout', function () {
    Auth::logout();

    return redirect()->route('login');
})->name('logout');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register.show');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

Route::get('/autocomplete-users', [UserController::class, 'autocompleteUsers']);

Route::post('/transfer', [TransferController::class, 'store'])->name('transfer.store');

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function autocompleteUsers(Request $request)
    {
        $query = $request->get('query');
        $users = User::where('name', 'like', "%$query%")->get();

        return response()->json($users);
    }
}

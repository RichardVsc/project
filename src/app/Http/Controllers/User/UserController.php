<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Autocomplete users based on the search query.
     *
     * This method accepts a search query and returns a list of users whose
     * names match the query. The results are returned as a JSON response.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocompleteUsers(Request $request)
    {
        $query = $request->get('query');
        $users = User::where('name', 'like', "%$query%")->get();

        return response()->json($users);
    }
}

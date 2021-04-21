<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLocationRequest;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;

class UserController extends Controller
{
    public function get(User $user)
    {
        return $user;
    }

    public function store(UserStoreRequest $request)
    {
        return User::create([
            'username' => $request->username,
            'location' => $request->location,
        ]);
    }

    public function update(UpdateLocationRequest $request, User $user)
    {
        $user->location = $request->location;
        $user->save();

        return $user;
    }
}

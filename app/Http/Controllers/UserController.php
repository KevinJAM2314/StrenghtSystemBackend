<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        User::create([
            'userName' => $request->userName,
            'password' => $request->password,
            'person_id' => $request->person_id
        ]);
    }
}

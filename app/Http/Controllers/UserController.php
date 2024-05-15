<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {   
        $this->validate($request, [
            'person.firstName' => 'required|max:20',
            'person.secondName' => 'max:20',
            'person.firstLastName' => 'required|max:20',
            'person.secondLastName' => 'required|max:20',
            'user.userName' => 'required|unique:users,username|min:3|max:20',
            'user.password' => 'required|min:6'
        ]);

        $person = Person::create([
            'firstName' => $request->person['firstName'],
            'secondName' => $request->person['secondName'],
            'firstLastName' => $request->person['firstLastName'],
            'secondLastName' => $request->person['secondLastName'],
            'type_person_id' => 1
        ]);

        User::create([
            'userName' => $request->user['userName'],
            'password' => Hash::make($request->user['password']),
            'person_id' => $person->id
        ]);
    }
}

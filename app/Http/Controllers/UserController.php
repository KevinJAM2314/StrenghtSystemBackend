<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function store(Request $request)
    {   
        $request->request->add(['user.userName' => Str::slug($request->user['userName'])]);
        try{
            $this->validate($request, [
                'person.firstName' => 'required|max:20',
                'person.secondName' => 'max:20',
                'person.firstLastName' => 'required|max:20',
                'person.secondLastName' => 'required|max:20',
                'person.gender' => 'required',
                'person.dateBirth' => 'before:today',
                'user.userName' => 'required|unique:users,username|min:3|max:20',
                'user.password' => 'required|min:6'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $person = Person::create([
            'firstName' => $request->person['firstName'],
            'secondName' => $request->person['secondName'],
            'firstLastName' => $request->person['firstLastName'],
            'secondLastName' => $request->person['secondLastName'],
            'gender' => $request->person['gender'],
            'dateBirth' => $request->person['dateBirth'],
            'type_person_id' => 1
        ]);

        User::create([
            'userName' => $request->user['userName'],
            'password' => Hash::make($request->user['password']),
            'person_id' => $person->id
        ]);

        return response()->json(['message' => 'Admin creado correctamente, espera a que te confirmen']); 
    }

    public function verify(Request $request)
    {
        try {
            $this->validate($request, [
                'userName' => 'required',
                'password' => 'required'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()]);
        }

        $user = User::where('userName', $request->userName)->first();

        if(auth()->attempt($request->only('userName', 'password'), $request->remember) || $user->confirmated){
            return response()->json(['message' => 'Credenciales correctas', 'userName' => $user->userName]);
        }

        return response()->json(['message' => 'Credenciales incorrectas o la cuenta no esta confirmada']); 
    }
}

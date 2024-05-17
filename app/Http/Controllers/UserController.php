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
            return response()->json(['errors' => $e->validator->errors()], 422);
        }

        $user = User::where('userName', $request->userName)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Assuming you are using Laravel Sanctum or a similar package for API tokens
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['message' => 'Credenciales correctas', 'userName' => $user->userName, 'token' => $token]);
        }

        return response()->json(['message' => 'Credenciales incorrectas o la cuenta no esta confirmada'], 401);
    }
}

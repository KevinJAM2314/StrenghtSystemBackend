<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Lang;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('confirmated', 0)->select('id', 'userName')->get();

        return response()->json(['users' => $users]); 
    }
    
    public function store(Request $request)
    {   
        $request->request->add(['user.userName' => Str::slug($request->user['userName'])]);
        try{
            $this->validate($request, [
                'person.firstName' => 'required|string|max:20',
                'person.secondName' => 'nullable|max:20',
                'person.firstLastName' => 'required|string|max:20',
                'person.secondLastName' => 'nullable|max:20',
                'person.gender' => 'required|boolean',
                'person.dateBirth' => 'nullable|date|before:today',  
                'user.userName' => 'required|string|unique:users,username|min:3|max:20',
                'user.password' => 'required|string|min:6'
            ]);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);

            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])]);
        }

        $person = Person::create([
            'firstName' => $request->person['firstName'],
            'secondName' => $request->person['secondName'] ?? null,
            'firstLastName' => $request->person['firstLastName'],
            'secondLastName' => $request->person['secondLastName'] ?? null,
            'gender' => $request->person['gender'],
            'dateBirth' => $request->person['dateBirth'] ?? null,
            'type_person_id' => 1
        ]);

        User::create([
            'userName' => $request->user['userName'],
            'password' => Hash::make($request->user['password']),
            'person_id' => $person->id
        ]);

        return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
        'message' => Lang::get('messages.alerts.message.create', ['table' => 'User'])]); 
    }

    public function verify(Request $request)
    {
        try {
            $this->validate($request, [
                'userName' => 'required',
                'password' => 'required'
            ]);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            
            $errorMessages = implode('*', $errors);

            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])]);
        }

        $user = User::where('userName', $request->userName)->first();

        if (auth()->attempt($request->only('userName', 'password'), $request->remember) || $user->confirmated) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'Credenciales correctas',
                'userName' => $user->userName,
                'token' => $token
            ]);
        } else {
            return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
            'message' => Lang::get('messages.alerts.message.error_verify', ['table' => 'User'])]);
        }  
    }

    public function confirmated(Request $request)
    {
        $user = User::find($request->id);
        if($user){
            $user->update([
                'confirmated' => true
            ]);
            return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
            'message' => Lang::get('messages.alerts.message.confirmated', ['table' => 'User'])]);
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'User'])]); 
    }

    public function destroy(Request $request)
    {
        $user = User::find($request->id);
        if($user){
            $user->destroy();
            return response()->json(['title' => Lang::get('messages.alerts.title.success'), 
            'message' => Lang::get('messages.alerts.message.destoy', ['table' => 'User'])]); 
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'), 
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'User'])]); 
    }
}


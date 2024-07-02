<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('confirmated', 0)->select('id', 'userName')->get();

        return response()->json(['users' => $users], 200);
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
            'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])], 400);
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
        'message' => Lang::get('messages.alerts.message.create', ['table' => 'User'])], 201);
    }

    public function update(Request $request)
    {
        $request->request->add(['user.userName' => Str::slug($request->user['userName'])]);
        
        try {
            $this->validate($request, [
                'person.firstName' => 'required|string|max:20',
                'person.secondName' => 'nullable|max:20',
                'person.firstLastName' => 'required|string|max:20',
                'person.secondLastName' => 'nullable|max:20',
                'person.gender' => 'required|boolean',
                'person.dateBirth' => 'nullable|date|before:today',
                'user.userName' => 'required|string|min:3|max:20',
                'user.password' => 'required|string|min:6',
                'user.newPass' => 'nullable|string|min:6',
                'user.newPassConfirm' => 'nullable|string|min:6|same:user.newPass',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $errorMessages = implode('*', $errors);

            return response()->json([
                'title' => Lang::get('messages.alerts.title.error'),
                'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])
            ], 400);
        }

        $person = Person::find($request->id);

        if (!$person) {
            return response()->json([
                'title' => Lang::get('messages.alerts.title.error'), 
                'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'User'])
            ], 404);
        }

        // Iniciar transacción
        DB::beginTransaction();
        
        try {
            // Actualizar los datos de la persona
            $person->update([
                'firstName' => $request->person['firstName'],
                'secondName' => $request->person['secondName'] ?? null,
                'firstLastName' => $request->person['firstLastName'],
                'secondLastName' => $request->person['secondLastName'] ?? null,
                'gender' => $request->person['gender'],
                'dateBirth' => $request->person['dateBirth'] ?? null,
                'type_person_id' => 1
            ]);

            // Buscar el usuario
            $user = User::where('person_id', $request->id)->first();

            // Validar la contraseña actual
            if (!Hash::check($request->user['password'], $user->password)) {
                return response()->json([
                    'title' => Lang::get('messages.alerts.title.error'),
                    'message' => Lang::get('messages.alerts.message.invalid_password')
                ], 400);
            }

            // Preparar los datos a actualizar
            $userData = [];
            
            // Si el nombre de usuario es diferente, agregarlo a los datos a actualizar
            if ($user->userName !== $request->user['userName']) {
                $userData['userName'] = $request->user['userName'];
            }

            if(!empty($request->user['newPass']) && $request->user['newPass'] !== $request->user['newPassConfirm']) {
                return response()->json([
                    'title' => Lang::get('messages.alerts.title.error'),
                    'message' => Lang::get('messages.alerts.message.new_password_diferent')
                ], 400);
            }

            // Si la nueva contraseña y la confirmación coinciden, agregar la nueva contraseña a los datos a actualizar
            if (!empty($request->user['newPass']) && $request->user['newPass'] === $request->user['newPassConfirm']) {
                $userData['password'] = Hash::make($request->user['newPass']);
            }

            // Actualizar los datos del usuario si hay algo que actualizar
            if (!empty($userData)) {
                $user->update($userData);
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'title' => Lang::get('messages.alerts.title.success'),
                'message' => Lang::get('messages.alerts.message.update', ['table' => 'User'])
            ], 201);
        } catch (\Exception $e) {
            // Deshacer la transacción en caso de error
            DB::rollBack();
            return response()->json([
                'title' => Lang::get('messages.alerts.title.error'),
                'message' => Lang::get('messages.alerts.message', ['table' => 'User']),
                'error' => $e->getMessage()
            ], 500);
        }
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
                'message' => Lang::get('messages.alerts.message.error', ['error' => $errorMessages])], 400);
        }

        $user = User::where('userName', $request->userName)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->confirmated) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Credenciales correctas',
                    'userName' => $user->userName,
                    'token' => $token,
                    'id' => $user->id,
                ]);
            } else {
                return response()->json(['title' => Lang::get('messages.alerts.title.error'),
                    'message' => Lang::get('messages.alerts.message.not_confirmated')], 400);
            }
        } else {
            return response()->json(['title' => Lang::get('messages.alerts.title.error'),
                'message' => Lang::get('messages.alerts.message.error_verify')], 401);
        }
    }

    public function show(Request $request)
    {
        $userInfo = User::where('id', $request->id)->select('userName','created_at', 'person_id')->first();
    
        if (!$userInfo) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $person = Person::where('id', $userInfo->person_id)->first();

        return response()->json(['user' => $userInfo, 'person' => $person], 200);
    }
    

    public function confirmated(Request $request)
    {
        $user = User::find($request->id);
        if($user){
            $user->confirmated = true;
            $user->save();
            return response()->json(['title' => Lang::get('messages.alerts.title.success'),
            'message' => Lang::get('messages.alerts.message.confirmated', ['table' => 'User'])], 200);
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'),
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'User'])], 404);
    }

    public function destroy(Request $request)
    {
        $user = User::find($request->id);
        if($user){
            $user->delete();
            return response()->json(['title' => Lang::get('messages.alerts.title.success'),
            'message' => Lang::get('messages.alerts.message.delete', ['table' => 'User'])], 204);
        }
        return response()->json(['title' => Lang::get('messages.alerts.title.error'),
        'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'User'])], 404);
    }
}


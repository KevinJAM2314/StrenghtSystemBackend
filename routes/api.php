<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users/login', [UserController::class, 'verify']);
Route::post('/users', [UserController::class, 'store']);

Route::get('/clients/create', [ClientController::class, 'create']); // Cuando se llama el formulario
Route::post('/clients', [ClientController::class, 'store']);
Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
Route::put('/clients/{id}', [ClientController::class, 'update']);
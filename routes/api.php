<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/user/store', 'App\Http\Controllers\Api\UserController@store');

// Route::get('users/get/{flag}', [UserController::class, 'index']);
Route::get('users/get', [UserController::class, 'index']);

Route::get('users/{id}', [UserController::class, 'show']);

Route::put('update/{id}', [UserController::class, 'update']);

Route::delete('users/delete/{id}', [UserController::class, 'destroy']);

Route::patch('change-password/{id}', [UserController::class, 'changePassword']);

Route::post('/register', [UserController::class, 'register']);

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function(){
    Route::get('/user/{id}',[UserController::class, 'getUser']);
});

Route::get('/test', function(){
    p("Working");
});  


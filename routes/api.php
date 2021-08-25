<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [\App\Http\Controllers\UserController::class,'register']);
Route::post('login', [\App\Http\Controllers\UserController::class,'authenticate']);
Route::get('open', [\App\Http\Controllers\UserController::class,'open']);
Route::post('contact',[\App\Http\Controllers\ContactController::class,'store']);
Route::post('appointment',[\App\Http\Controllers\AppointmentsController::class,'store']);
Route::post('contact/update',[\App\Http\Controllers\ContactController::class,'update']);
Route::post('appointment/update',[\App\Http\Controllers\AppointmentsController::class,'update']);

Route::get('appointment',[\App\Http\Controllers\AppointmentsController::class,'index']);
Route::get('appointment/{id}',[\App\Http\Controllers\AppointmentsController::class,'getById']);
Route::delete('appointment/{id}',[\App\Http\Controllers\AppointmentsController::class,'deleteById']);
Route::get('contact',[\App\Http\Controllers\ContactController::class,'index']);
Route::get('contact/{id}',[\App\Http\Controllers\ContactController::class,'getById']);
Route::delete('contact/{id}',[\App\Http\Controllers\ContactController::class,'deleteById']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('user', [\App\Http\Controllers\UserController::class,'getAuthenticatedUser']);
    Route::get('closed', [\App\Http\Controllers\UserController::class,'closed']);
});

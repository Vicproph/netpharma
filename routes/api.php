<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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
Route::resource('users', UserController::class);
Route::post('users/login', UserController::class . '@login');
Route::middleware('auth:api')->post('users/logout', UserController::class . '@logout');

Route::middleware('auth:api')->resource('posts', PostController::class);
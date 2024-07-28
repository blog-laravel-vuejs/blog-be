<?php

use App\Http\Controllers\AdminController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Admin
Route::prefix('admin')->controller(AdminController::class)->group(function () {
    Route::post('login', 'login');
    Route::middleware('auth:admin_api')->group(function () {
        Route::get('profile', 'profile');
        Route::get('logout', 'logout');
        Route::post('update', 'updateProfile');
        Route::post('add-user', 'addUser');
    });

});

// User
Route::prefix('user')->controller(UserController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('forgot-pw-sendcode', 'forgotPassword');
    Route::post('forgot-update', 'forgotUpdate');
    Route::middleware('auth:user_api')->group(function () {
        Route::get('logout', 'logout');
        Route::get('profile', 'profile');
        Route::post('update', 'updateProfile');
        Route::post('change-password', 'changePassword');
    });
});

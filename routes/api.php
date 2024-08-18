<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
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
        Route::get('users', 'getUsers');
        Route::post('block-user/{id_user}', 'changeIsBlockUser');
        Route::post('block-many-user', 'changeIsBlockManyUser');
    });
    Route::middleware(['check.auth:admin_api', 'role:manager'])->group(function () {
        Route::post('add-member', 'addMember');
        Route::get('members', 'getMembers');
        Route::post('change-role/{id_admin}', 'changeRole');
        Route::post('delete-member/{id_member}', 'deleteMember');
        Route::post('delete-many-member', 'deleteManyMember');
       
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
// Category
Route::prefix('category')->controller(CategoryController::class)->group(function () {
    Route::middleware('auth:admin_api')->group(function () {
        Route::post('add', 'add');
        Route::post('update/{id_category}', 'update');
        Route::post('delete/{id_category}', 'delete');
        Route::post('deletes', 'deleteMany');
    });
    Route::get('/', 'all');
});


Route::prefix('article')->controller(ArticleController::class)->group(function () {
    Route::middleware('auth:user_api')->group(function () {
        Route::post('add', 'add');
        Route::get('my-article', 'myArticle');
        Route::post('update/{id_article}', 'update');
        Route::post('change-is-show/{id_article}', 'changeIsShow');
        Route::post('delete/{id_article}', 'delete');
    });
    Route::middleware('auth:admin_api')->group(function () {
        Route::get('all', 'getAll');
        Route::post('change-is-accept/{id_article}', 'changeIsAccept');
    });
    Route::get('/', 'articleHome');
    Route::get('/{id_article}', 'detail');
});
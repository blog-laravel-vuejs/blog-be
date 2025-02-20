<?php

namespace App\Http\Controllers;
use App\Http\Requests\RequestLogin;
use App\Http\Requests\RequestUserRegister;
use App\Services\UserService;


class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login(RequestLogin $request)
    {
        return $this->userService->login($request);
    }

    public function logout()
    {
        auth('user_api')->logout();

        return response()->json([
            'message' => 'Đăng xuất thành công !',
            'status' => 200,
        ], 200);
    }
    public function register(RequestUserRegister $request)
    {
        return $this->userService->userRegister($request);
    }
    

   
}

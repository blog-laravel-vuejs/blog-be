<?php

namespace App\Http\Controllers;
use App\Http\Requests\RequestCreatePassword;
use App\Http\Requests\RequestLogin;
use App\Http\Requests\RequestSendForgot;
use App\Http\Requests\RequestUpdateProfileUser;
use App\Http\Requests\RequestUserRegister;
use App\Services\UserService;
use Illuminate\Http\Request;

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

    public function logout(Request $request)
    {
        return $this->userService->logout($request);
    }
    public function register(RequestUserRegister $request)
    {
        return $this->userService->userRegister($request);
    }
    // verify email
    public function verifyEmail(Request $request)
    {
        return $this->userService->verifyEmail($request);
    }
    // forgot password
    public function forgotPassword(RequestSendForgot $request)
    {
        return $this->userService->forgotPassword($request);
    }

    public function forgotUpdate(RequestCreatePassword $request)
    {
        return $this->userService->forgotUpdate($request);
    }
    public function profile(Request $request)
    {
        return $this->userService->profile($request);
    }
    public function updateProfile(RequestUpdateProfileUser $request)
    {
        return $this->userService->updateProfile($request);
    }
}

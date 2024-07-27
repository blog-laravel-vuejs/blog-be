<?php

namespace App\Http\Controllers;



use App\Http\Requests\RequestLogin;

use App\Services\AdminService;
use Illuminate\Http\Request;
use App\Http\Requests\RequestUpdateProfileAdmin;
class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function login(RequestLogin $request)
    {
        return $this->adminService->login($request);
    }

    public function logout(Request $request)
    {
        return $this->adminService->logout($request);
    }
    public function profile(Request $request)
    {
        return $this->adminService->profile($request);
    }
    public function updateProfile(RequestUpdateProfileAdmin $request)
    {
        return $this->adminService->updateProfile($request);
    }
}

<?php

namespace App\Http\Controllers;


use App\Http\Requests\RequestLogin;

use App\Services\AdminService;

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

    public function profile()
    {
        return response()->json([
            'message' => 'Xem thông tin cá nhân thành công !',
            'data' => auth('admin_api')->user(),
            'status' => 200,
        ], 200);
    }

    public function logout()
    {
        auth('admin_api')->logout();

        return response()->json([
            'message' => 'Đăng xuất thành công !',
            'status' => 200,
        ], 200);
    }

}

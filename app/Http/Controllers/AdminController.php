<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestAddUser;
use App\Http\Requests\RequestChangeIsBlock;
use App\Http\Requests\RequestChangeIsBlockMany;
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
    public function addUser(RequestAddUser $request)
    {
        return $this->adminService->addUser($request);
    }
    public function getUsers(Request $request)
    {
        return $this->adminService->getUsers($request);
    }
    public function changeIsBlockUser(RequestChangeIsBlock $request, $id_user)
    {
        return $this->adminService->changeIsBlockUser($request, $id_user);
    }
    public function changeIsBlockManyUser(RequestChangeIsBlockMany $request)
    {
        return $this->adminService->changeIsBlockManyUser($request);
    }
    
}

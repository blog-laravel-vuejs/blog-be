<?php

namespace App\Services;

use App\Http\Requests\RequestChangePassword;
use App\Http\Requests\RequestLogin;
use App\Http\Requests\RequestUpdateProfileAdmin;
use App\Models\Admin;
use App\Repositories\AdminInterface;
use App\Traits\APIResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AdminService
{
    use APIResponse;

    protected AdminInterface $adminRepository;

    public function __construct(
        AdminInterface $adminRepository
    ) {
        $this->adminRepository = $adminRepository;
       
    }

    public function login(RequestLogin $request)
    {
        try {
            $admin = Admin::where('email', $request->email)->first();
            if (empty($admin)) {
                return $this->responseError('Email does not exist !', 404);
            }
            $credentials = request(['email', 'password']);
            if (!$token = auth()->guard('admin_api')->attempt($credentials)) {
                return $this->responseError('Email or password is incorrect!');
            }
            $admin->access_token = $token;
            $admin->token_type = 'bearer';
            $admin->expires_in = auth()->guard('admin_api')->factory()->getTTL() * 60;

            return $this->responseSuccessWithData($admin, 'Logged in successfully !');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            auth('admin_api')->logout();

            return $this->responseSuccess('Log out successfully !');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }
    public function profile(Request $request)
    {
        try {
            $admin = auth('admin_api')->user();

            return $this->responseSuccessWithData($admin, 'Get information account successfully !');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }
    public function updateProfile(RequestUpdateProfileAdmin $request)
    {
        DB::beginTransaction();
        try {
            $user = Admin::find(auth('admin_api')->user()->id);
            if ($request->hasFile('avatar')) {
                // upload file
                $image = $request->file('avatar');
                $uploadedFile = Cloudinary::upload($image->getRealPath(), ['folder' => 'avatars/admin', 'resource_type' => 'auto']);
                $avatar = $uploadedFile->getSecurePath();
                // delete old file
                if ($user->avatar) {
                    $id_file = explode('.', implode('/', array_slice(explode('/', $user->avatar), 7)))[0];
                    Cloudinary::destroy($id_file);
                }
                // upload profile
                $data = array_merge($request->all(), ['avatar' => $avatar]);
                $user->update($data);
            } else {
                $request['avatar'] = $user->avatar;
                $user->update($request->all());
            }

            DB::commit();

            return $this->responseSuccessWithData($user, 'Update profile successful !');
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage(), 400);
        }
    }
    public function changePassword(RequestChangePassword $request)
    {
        DB::beginTransaction();
        try {
            $admin =Admin::find(auth('admin_api')->user()->id);
            if (!(Hash::check($request->get('current_password'), $admin->password))) {
                return $this->responseError('Your password is incorrect !');
            }
            $data = ['password' => Hash::make($request->get('new_password'))];
            $admin->update($data);
            DB::commit();

            return $this->responseSuccess('Password change successful !');
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage());
        }
    }    
}

<?php

namespace App\Services;

use App\Enums\UserEnum;
use App\Http\Requests\RequestCreatePassword;
use App\Http\Requests\RequestLogin;
use App\Http\Requests\RequestSendForgot;
use App\Http\Requests\RequestUpdateProfileUser;
use App\Http\Requests\RequestUserRegister;
use App\Jobs\SendForgotPassword;
use App\Models\PasswordReset;
use App\Models\User;

use App\Repositories\UserInterface;
use App\Repositories\UserRepository;
use App\Traits\APIResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

use Throwable;

class UserService
{
    use APIResponse;
    protected UserInterface $userRepository;

    public function __construct(
        UserInterface $userRepository,
    ) {
        $this->userRepository = $userRepository;
    }
    public function login(RequestLogin $request)
    {
        try {
            $user = $this->userRepository->findUserByEmail($request->email);
            if (empty($user)) {
                return $this->responseError('Email does not exist !');
            }
            if ($user->is_block != 1) {
                return $this->responseError('Your account has been locked !');
            }

            $credentials = request(['email', 'password']);
            if (!$token = auth()->guard('user_api')->attempt($credentials)) {
                return $this->responseError('Email or password is incorrect!');
            }

            $user->access_token = $token;
            $user->token_type = 'bearer';
            $user->expires_in = auth()->guard('user_api')->factory()->getTTL() * 60;

            return $this->responseSuccessWithData($user, 'Logged in successfully !');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }

    }
    public function userRegister(RequestUserRegister $request)
    {
        try {
            // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu hay chưa
            $userEmail = $this->userRepository->findUserbyEmail($request->email);
            $userName = $this->userRepository->findUserbyUserName($request->username);
            if ($userEmail) {
                return $this->responseError('Account existed!', 400);
            }
            if ($userName) {
                return $this->responseError('Username existed!',400 );
            }

            // Khởi tạo mảng dữ liệu người dùng từ request
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'username' => $request->username,
                'is_block' => 0,
            ];

            // Tạo người dùng mới
            $user = $this->userRepository->createUser((object)$data);
            return $this->responseSuccess('Register successfully! Login now continue discovery blog !', 201);
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage(),400);
        }
    }

    // send code forgot password
    public function forgotPassword(RequestSendForgot $request)
    {
        DB::beginTransaction();
        try {
            $email = $request->email;
            $findUser = User::where('email', $email)->first();
            if (empty($findUser)) {
                return $this->responseError('No account found in the system !', 400);
            }
            $token = Str::random(32);
            $role = 'user';
            $user = PasswordReset::where('email', $email)->where('role', $role)->first();
            if ($user) {
                $user->update(['token' => $token]);
            } else {
                PasswordReset::create(['email' => $email, 'token' => $token, 'role' => $role]);
            }
            $url = UserEnum::FORGOT_FORM_USER . $token;
            Log::info("Add jobs to Queue , Email: $email with URL: $url");
            Queue::push(new SendForgotPassword($email, $url));
            DB::commit();

            return $this->responseSuccess('Password reset email sent successfully, please check your email !', 201);
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage(), 400);
        }
    }

    public function forgotUpdate(RequestCreatePassword $request)
    {
        DB::beginTransaction();
        try {
            $token = $request->token ?? '';
            $new_password = Hash::make($request->new_password);
            $passwordReset = PasswordReset::where('token', $token)->first();
            if ($passwordReset) {
                $user = User::where('email', $passwordReset->email);
                if ($passwordReset->role == 'user' && !empty($user)) {
                    $user->update(['password' => $new_password]);
                    $passwordReset->delete();

                    DB::commit();

                    return $this->responseSuccess('Reset password successfully !');
                } else {
                    return $this->responseError('Account not found !', 404);
                }
            } else {
                DB::commit();

                return $this->responseError('Token has expired !', 400);
            }
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage(), 400);
        }
    }
    public function logout(Request $request)
    {
        try {
            auth('user_api')->logout();

            return $this->responseSuccess('Log out successfully !');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }
    public function profile(Request $request)
    {
        try {
            $user = auth('user_api')->user();

            return $this->responseSuccessWithData($user, 'Get information account successfully !');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }
    public function updateProfile(RequestUpdateProfileUser $request)
    {
        DB::beginTransaction();
        try {
            $user = User::find(auth('user_api')->user()->id);
            if ($request->hasFile('avatar')) {
                // upload file
                $image = $request->file('avatar');
                $uploadedFile = Cloudinary::upload($image->getRealPath(), ['folder' => 'avatars/users', 'resource_type' => 'auto']);
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

}



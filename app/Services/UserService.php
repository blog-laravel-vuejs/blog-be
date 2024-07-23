<?php

namespace App\Services;

use App\Enums\UserEnum;
use App\Http\Requests\RequestLogin;
use App\Http\Requests\RequestUserRegister;
use App\Models\User;

use App\Repositories\UserInterface;
use App\Repositories\UserRepository;
use Brian2694\Toastr\Facades\Toastr;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Throwable;

class UserService
{
    protected UserInterface $userRepository;

    public function __construct(
        UserInterface $userRepository,
    ) {
        $this->userRepository = $userRepository;
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('user_api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('user_api')->factory()->getTTL() * 60,
        ]);
    }

    public function responseOK($status = 200, $data = null, $message = '')
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => $status,
        ], $status);
    }

    public function responseError($status = 400, $message = '')
    {
        return response()->json([
            'message' => $message,
            'status' => $status,
        ], $status);
    }

    public function login(RequestLogin $request)
    {
        try {
            $user = $this->userRepository->findUserByEmail($request->email);
            if (empty($user)) {
                return $this->responseError(400, 'Email không tồn tại !');
            } else {
                $is_block = $user->is_block;
                if ($is_block != 1) {
                    return $this->responseError(400, 'Tài khoản của bạn đã bị khóa hoặc chưa được phê duyệt !');
                }
                if ($user->email_verified_at == null) {
                    return $this->responseError(400, 'Email này chưa được xác nhận , hãy kiểm tra và xác nhận nó trước khi đăng nhập !');
                }
            }

            $credentials = request(['email', 'password']);
            if (!$token = auth()->guard('user_api')->attempt($credentials)) {
                return $this->responseError(400, 'Email hoặc mật khẩu không chính xác !');
            }

            $user->access_token = $token;
            $user->token_type = 'bearer';
            $user->expires_in = auth()->guard('user_api')->factory()->getTTL() * 60;

            // return $this->responseSuccessWithData($user, 'Logged in successfully !');
            return $this->responseOK(200, $user, 'Đăng nhập thành công!');
        } catch (Throwable $e) {
            return $this->responseError(400, $e->getMessage());
        }

    }
    public function userRegister(RequestUserRegister $request)
    {
        try {
            // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu hay chưa
            $userEmail = $this->userRepository->findUserbyEmail($request->email);
            if ($userEmail) {
                return $this->responseError(400, 'Tài khoản đã tồn tại!');
            }

            // Khởi tạo mảng dữ liệu người dùng từ request
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'username' => $request->username,
                'avatar' => null,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'is_block' => 1,
            ];

            // Kiểm tra và xử lý việc upload avatar nếu có
            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $uploadedFile = Cloudinary::upload($image->getRealPath(), ['folder' => 'avatars', 'resource_type' => 'auto']);
                $data['avatar'] = $uploadedFile->getSecurePath();
            }

            // Tạo người dùng mới
            $user = $this->userRepository->createUser((object)$data);

            return $this->responseOK(200, $user, 'Đăng kí tài khoản thành công!');
        } catch (Throwable $e) {
            return $this->responseError(400, $e->getMessage());
        }
    }

    // public function userRegister(RequestUserRegister $request)
    // {
    //     try {
    //         $filter = (object) [
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //             'username' => $request->username,
    //             'avatar' => $request->avatar,
    //             'gender' => $request->gender,
    //             'phone' => $request->phone,
    //             'address'=>$request->address,
    //             'date_of_birth' =>$request->date_of_birth,
    //             'is_block' => 1,

    //         ];
    //         $userEmail = $this->userRepository->findUserbyEmail($request->email);
    //         if ($userEmail) {
    //             return $this->responseError(400, 'Tài khoản đã tồn tại !');
    //         } else {
    //             if ($request->hasFile('avatar')) {
    //                 // upload file
    //                 $image = $request->file('avatar');
    //                 $uploadedFile = Cloudinary::upload($image->getRealPath(), ['folder' => 'avatars', 'resource_type' => 'auto']);
    //                 $avatar = $uploadedFile->getSecurePath();
    //                 // upload profile
    //                 $data = array_merge($request->all(), ['avatar' => $avatar]);
    //                 // $user->update($data);
    //             } 
    //             // $filter->avatar = $this->saveAvatar($filter);
    //             $user = $this->userRepository->createUser($filter);

    //             return $this->responseOK(200, $user, 'Đăng kí tài khoản thành công !');
    //         }
    //     } catch (Throwable $e) {
    //         return $this->responseError(400, $e->getMessage());
    //     }
    // }
}



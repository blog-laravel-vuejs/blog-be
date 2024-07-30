<?php

namespace App\Services;

use App\Http\Requests\RequestAddUser;
use App\Http\Requests\RequestChangeIsBlock;
use App\Http\Requests\RequestChangeIsBlockMany;
use App\Http\Requests\RequestChangePassword;
use App\Http\Requests\RequestLogin;
use App\Http\Requests\RequestUpdateProfileAdmin;
use App\Jobs\SendMailNotify;
use App\Models\Admin;
use App\Models\User;
use App\Repositories\AdminInterface;
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
            $admin = Admin::find(auth('admin_api')->user()->id);
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
    public function addUser(RequestAddUser $request)
    {
        DB::beginTransaction();
        try {
            $new_password = Str::random(8);
            $data = array_merge($request->all(), [
                'password' => Hash::make($new_password),
                'is_block' => 0,
                'email_verified_at' => now(),
            ]);

            $user = User::create($data);

            $content = 'Below is your account information, please use it to log in to the system, then change your 
            password to ensure account security. <br> Email : <strong>' . $user->email .
                '</strong> <br> Password : <strong>' . $new_password . '</strong>';

            Queue::push(new SendMailNotify($user->email, $content));

            DB::commit();

            return $this->responseSuccessWithData($user, 'Added user account successfully !');
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage());
        }
    }
    public function getUsers(Request $request)
    {
        try {
            $orderBy = $request->typesort ?? 'id';
            switch ($orderBy) {
                case 'name':
                    $orderBy = 'name';
                    break;

                case 'address':
                    $orderBy = 'address';
                    break;

                case 'phone':
                    $orderBy = 'phone';
                    break;

                case 'gender':
                    $orderBy = 'gender';
                    break;

                case 'new':
                    $orderBy = 'id';
                    break;

                default:
                    $orderBy = 'id';
                    break;
            }

            $orderDirection = $request->sortlatest ?? 'true';
            switch ($orderDirection) {
                case 'true':
                    $orderDirection = 'DESC';
                    break;

                default:
                    $orderDirection = 'ASC';
                    break;
            }

            $filter = (object) [
                'search' => $request->search ?? '',
                'is_block' => $request->is_block ?? 'all',
                'orderBy' => $orderBy,
                'orderDirection' => $orderDirection,
            ];

            $users = UserRepository::getAllUsers($filter);
            if (!(empty($request->paginate))) {
                $users = $users->paginate($request->paginate);
            } else {
                $users = $users->get();
            }

            return $this->responseSuccessWithData($users, 'Get managers information successfully!');
        } catch (Throwable $e) {
            return $this->responseError($e->getMessage());
        }
    }
    public function changeIsBlockUser(RequestChangeIsBlock $request, $id_user)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id_user);
            if ($user) {
                $user->update(['is_block' => $request->is_block]);

                if ($request->is_block == 0) $content = '<strong style="color:red">Your account has been locked by admin, if you think this is a mistake please contact the system !</strong>';
                else $content = '<strong style="color:green">Your account has been unlocked !</strong>';

                Queue::push(new SendMailNotify($user->email, $content));

                DB::commit();

                return $this->responseSuccessWithData($user, 'Change is block manager successfully !');
            } else {
                DB::commit();

                return $this->responseError(404, 'Not found manager !');
            }
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage());
        }
    }

    public function changeIsBlockManyUser(RequestChangeIsBlockMany $request)
    {
        DB::beginTransaction();
        try {
            User::whereIn('id', $request->ids_user)->update(['is_block' => $request->is_block]);
            $users = User::whereIn('id', $request->ids_user)->get();
            if ($request->is_block == 0) $content = '<strong style="color:red">Your account has been locked by admin, if you think this is a mistake please contact the system !</strong>';
            else $content = '<strong style="color:green">Your account has been unlocked !</strong>';
            foreach ($users as $user) Queue::push(new SendMailNotify($user->email, $content));

            DB::commit();

            return $this->responseSuccess('Change is block many manager successfully !');
        } catch (Throwable $e) {
            DB::rollback();

            return $this->responseError($e->getMessage());
        }
    }

}

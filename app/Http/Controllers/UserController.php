<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\LoginUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function __construct(
        UserRepository $userRepository,
    ) {
        $this->repository = $userRepository;
        $this->requests = [
            'create' => new CreateUserRequest,
            'update' => new UpdateUserRequest,
            'login' => new LoginUserRequest
        ];
    }

    public function login()
    {
        request()->validate(($this->requests['login']->rules()));
        $creds = request()->only(['email', 'password']);

        if (Auth::attempt($creds)) {

            $userArray = $this->generatePassportToken($creds);
            return response()->json($userArray);
        } else
            return response()->json(['message' => __('auth.login.fail')], 422);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json(['message' => __('auth.logout.success')]);
    }

    private function  generatePassportToken(array $creds): array
    { // generates Passport token and serializes user and its token to an array
        /**
         * @var User $user
         */
        $user = User::where('email', $creds['email'])->first();
        $token = $user->createToken($creds['email'])->accessToken;

        $userArray = $user->toArray();
        $userArray['token'] = $token;
        return $userArray;
    }
}

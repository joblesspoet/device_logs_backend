<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use App\Traits\GetAuthUserTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\API\ForgotRequest;
use App\Http\Requests\API\Auth\LoginRequest;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Validation\ValidationException;
use App\Notifications\PasswordResetNotification;
use App\Http\Controllers\API\Traits\GenerateToken;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use App\Http\Requests\API\Auth\ForgotPasswordRequest;

class AuthController extends Controller {

    use GenerateToken, GetAuthUserTrait;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {

        $this->middleware('auth:sanctum', [
                'except' => ['login',
                            'forgot',
                            'resetPassword']
                ]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse
     */
    public function login(LoginRequest $request) {

        $user =  User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        $token = $user->createToken($request->email)->plainTextToken;

        return $this->respondWithToken($token, $user);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me() {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return array
     */
    public function logout() {
        $user = auth()->user();

        return DB::transaction(function () use ($user) {
            // revoke user current tokens
            $user->currentAccessToken()->delete();

            return response()->json(['message' => 'Successfully logged out']);
        });
    }

    /**
     * @param ForgotRequest $request
     * @return JsonResponse
     */
    public function forgot(ForgotPasswordRequest $request) {
        $attributes = $request->only(['email']);

        /** @var PasswordBrokerManager $password */
        $password = App::make('auth.password');

        /** @var PasswordBroker $passwordBroker */
        $passwordBroker = $password->broker();

        /** @var User $user */
        $user = $passwordBroker->getUser($attributes);

        if(!$user){
            return new JsonResponse([
                'message' => __('User does not exist.')
                    ], JsonResponse::HTTP_NOT_FOUND);
        }

        $token = $passwordBroker->createToken($user);

        $resetPasswordToken = $this->getToken();

        $user->update(['reset_password_token' => $resetPasswordToken]);

        $user->notify(new PasswordResetNotification($resetPasswordToken));

        return new JsonResponse([
            'message' => __('An email has been sent to you, Kindly follow the email.')
                ], JsonResponse::HTTP_OK);
    }

    /**
     * User password reset managed here (Forget password case)
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
    {

        $input = $request->only(
            'email',
            'password',
            'reset_password_token',
            'password_confirmation'
        );

        $user = User::where('email', $input['email'])->first();

        if(!$user){
            return new JsonResponse([
                'message' => __('User does not exist.')
                    ], JsonResponse::HTTP_NOT_FOUND);
        }

        $user->update(['reset_password_token' => null, 'password' => Hash::make($input['password'])]);

        $token = $user->createToken($input['email'])->plainTextToken;

        return $this->respondWithToken($token, $user->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @return array
     */
    protected function respondWithToken($token, $user) {
        return [
            'access_token' => $token,
            'user' => new UserResource($user)
        ];
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard
     */
    protected function getGuard() {
        return Auth::guard('api');
    }


}

<?php

namespace Modules\ApplicationAuth\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\ApplicationAuth\Entities\User;
use Modules\ApplicationAuth\Http\Requests\Auth\ChangePasswordRequest;
use Modules\ApplicationAuth\Http\Requests\Auth\ForgotPasswordRequest;
use Modules\ApplicationAuth\Http\Requests\Auth\RegisterRequest;
use Modules\ApplicationAuth\Http\Requests\Auth\ResetPasswordRequest;
use Modules\ApplicationAuth\Notifications\PasswordResetNotification;
use Modules\ApplicationAuth\Transformers\UserResource;
use Tymon\JWTAuth\Blacklist;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\Validators\PayloadValidator;
use App\Models\User as UserModel;

class AuthController extends Controller
{
    use ThrottlesLogins;

    /** @var \Illuminate\Contracts\Config\Repository */
    protected Config $config;

    /** @var \Illuminate\Contracts\Events\Dispatcher */
    protected Dispatcher $events;

    /** @var \Tymon\JWTAuth\Manager */
    protected Manager $jwt;

    /** @var \Tymon\JWTAuth\Blacklist */
    protected Blacklist $blacklist;

    /** @var \Tymon\JWTAuth\Validators\PayloadValidator */
    private PayloadValidator $validator;

    /** @var string */
    protected string $guardName;

    /** @var \Illuminate\Contracts\Auth\Guard|\Tymon\JWTAuth\JWTGuard|\Tymon\JWTAuth\JWT */
    protected Guard $guard;

    /** @var int|null */
    protected ?int $guestRefreshTtl;

    /** @var int|null */
    protected ?int $refreshTtl;

    /**
     * @param \Illuminate\Contracts\Auth\Factory $auth
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Tymon\JWTAuth\Manager $jwt
     * @param \Tymon\JWTAuth\Blacklist $blacklist
     * @param \Tymon\JWTAuth\Validators\PayloadValidator $validator
     */
    public function __construct(
        Auth $auth,
        Config $config,
        Dispatcher $events,
        Manager $jwt,
        Blacklist $blacklist,
        PayloadValidator $validator
    ) {
        $this->config = $config;
        $this->events = $events;
        $this->jwt = $jwt;
        $this->blacklist = $blacklist;
        $this->validator = $validator;

        $this->guardName = $config->get('application-auth.auth.guard', 'application');
        $this->guestRefreshTtl = $config->get('application-auth.auth.guest_refresh_ttl');
        $this->refreshTtl = $config->get('jwt.refresh_ttl');

        $this->guard = $auth->guard($this->guardName);

        $this->setupMiddleware($auth);
    }


    /**
     * Register a new user.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \Modules\ApplicationAuth\Http\Requests\Auth\RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function register(Hasher $hasher, RegisterRequest $request): JsonResponse
    {
        /** @var \Modules\ApplicationAuth\Entities\User $model */
        $model = $this->config->get('application-auth.models.user', User::class);

        $attributes = Arr::except($request->validated(), ['password']);

        $user = new $model();
        $user->fill($attributes);
        $user->password = $hasher->make($request->input('password'));
        $user->save();

        $this->events->dispatch(new Registered($user));

        $token = $this->guard->login($user);

        $this->events->dispatch(new Login($this->guardName, $user, false));

        return $this->tokenResponse($token, $user);
    }

    /**
     * Perform user login.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function login(Hasher $hasher, Request $request): JsonResponse
    {
        // Verify if there have been too many attempts
        $this->verifyThrottle($request);

        try {
            // Validate the request and extract the credentials
            $credentials = $request->validate(
                [
                    'email' => ['required', 'string'],
                    'password' => ['required', 'string'],
                ],
                [],
                [
                    'email' => trans('application-auth::auth.email'),
                    'password' => trans('application-auth::auth.password'),
                ]
            );
            // Perform login attempt
            if (!($token = $this->guard->attempt($credentials))) {
                // Throw failed login response
                throw ValidationException::withMessages(['email' => trans('auth.failed')])
                    ->status(JsonResponse::HTTP_UNAUTHORIZED);
            }

            /** @var \Modules\ApplicationAuth\Entities\User $user */
            $user = $this->guard->user();

            /** */
            if($user && ($user->isA(UserModel::ROLE_ADMIN) ||
            $user->isA(UserModel::ROLE_CUSTOMER))){
                throw ValidationException::withMessages(['email' => trans('auth.failed')])
                    ->status(JsonResponse::HTTP_UNAUTHORIZED);
            }

            if ($hasher->needsRehash($user->password)) {
                $user->password = $hasher->make($credentials['password']);
                $user->save();
            }

            // Reset the throttle
            $this->clearLoginAttempts($request);

            $this->events->dispatch(new Login($this->guardName, $user, false));

            // Return the token
            return $this->tokenResponse($token, $user);
        } catch (\Throwable $t) {
            // On any error, increment the login attempts (this catches both validation and verification errors)
            $this->incrementLoginAttempts($request);

            // Rethrow the exception so client can handle it
            throw $t;
        }
    }

    /**
     * The username to use for the authentication throttle.
     *
     * @return string
     */
    public function username(): string
    {
        return 'email';
    }
    /**
     * Perform a token refresh.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $this->setTokenRefresh();

            $originalTokenId = null;
            try {
                $this->jwt->setRefreshFlow(true);

                $originalTokenId = $this->guard->getPayload()->get('jti');
            } catch (\Throwable $t) {
            } finally {
                $this->jwt->setRefreshFlow(false);
            }

            $token = $this->guard->refresh();

            /** @var \Modules\ApplicationAuth\Entities\User $user */
            $user = $this->guard->setToken($token)->user();

            return $this->tokenResponse($token, $user);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (JWTException $e) {
            // Any JWT exception means that the token is invalid and thus cannot be used anymore.
        } finally {
            $this->setTokenRefresh(true);
        }

        throw new AuthenticationException();
    }

    /**
     * Invalidate the token of the currently logged in user.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function logout(): JsonResponse
    {
        /** @var \Modules\ApplicationAuth\Entities\ApplicationUser $user */
        $user = $this->guard->user();

        try {
            $this->guard->logout();

            $this->events->dispatch(new Logout($this->guardName, $user));
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (JWTException $exception) {
            // Any JWT exception means that the token is invalid and thus cannot be used anymore.
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Update the user's password.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \Modules\ApplicationAuth\Http\Requests\Auth\ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changePassword(Hasher $hasher, ChangePasswordRequest $request): JsonResponse
    {
        /** @var \Modules\ApplicationAuth\Entities\User $user */
        $user = $this->guard->user();

        $inputs = $request->validated();

        if (!$hasher->check($inputs['old_password'], $user->password)) {
            throw ValidationException::withMessages(
                [
                    'old_password' => [trans('application-auth::change_password.password_mismatch')],
                ]
            );
        }

        $user->password = $hasher->make($inputs['password']);
        $user->save();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Request a password reset email.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \Modules\ApplicationAuth\Http\Requests\Auth\ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function forgot(Hasher $hasher, ForgotPasswordRequest $request): JsonResponse
    {
        /** @var \Modules\ApplicationAuth\Entities\User $model */
        $model = $this->config->get('application-auth.models.user', ApplicationUser::class);
        $user = ($query = $model::query())
            ->where($query->qualifyColumn('email'), $request->input('email'))
            ->first();

        if (!$user || !$user->email) {
            throw ValidationException::withMessages(['email' => [trans('passwords.user')]]);
        }

        $resetPasswordToken = str_pad(random_int(1, 99999999), 8, '0', STR_PAD_LEFT);

        $user->fill(
            [
                'reset_password_token' => $hasher->make($resetPasswordToken),
                'reset_password_token_expires' => Carbon::now()->addMinutes(60),
            ]
        );
        $user->save();

        $user->notify(new PasswordResetNotification($resetPasswordToken));

        return new JsonResponse(['message' => trans('passwords.sent')]);
    }

    /**
     * Perform a password reset.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \Modules\ApplicationAuth\Http\Requests\Auth\ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword(Hasher $hasher, ResetPasswordRequest $request): JsonResponse
    {
        $attributes = $request->validated();

        /** @var \Modules\ApplicationAuth\Entities\User $model */
        $model = $this->config->get('application-auth.models.user', User::class);
        $user = ($query = $model::query())
            ->where('email', $attributes['email'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages(['email' => [trans('passwords.user')]]);
        }


        if (!$hasher->check($attributes['reset_password_token'], $user->reset_password_token) ||
            !$user->reset_password_token_expires ||
            $user->reset_password_token_expires->isPast()) {
            throw ValidationException::withMessages(['reset_password_token' => [trans('passwords.token')]]);
        }

        $user->fill(
            [
                'reset_password_token' => null,
                'reset_password_token_expires' => null,
                'password' => $hasher->make($attributes['password']),
            ]
        );

        $user->save();

        $token = $this->guard->login($user);

        return $this->tokenResponse($token, $user);
    }

    /**
     * Throttle the authentication requests and lock out the user if they exceed the threshold.
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function verifyThrottle(Request $request): void
    {
        // Verify if there have been too many attempts
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);
        }
    }

    /**
     * Return the token and additional info as JSON response.
     *
     * @param string $token
     * @param \Modules\ApplicationAuth\Entities\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tokenResponse(string $token, User $user): JsonResponse
    {
        return new JsonResponse($this->getTokenResponseData($token, $user));
    }

    /**
     * Return the token and additional info.
     *
     * @param string $token
     * @param \Modules\ApplicationAuth\Entities\User $user
     * @return array
     */
    protected function getTokenResponseData(string $token, User $user): array
    {
        if (!($payload = $this->getPayload($token, false))) {
            abort(500, "Failed decoding fresh token.");
        }

        $expiresAt = $payload->get('exp');

        $refreshExpiresAt = Carbon::createFromTimestampUTC($payload->get('iat'))
            ->addMinutes($this->refreshTtl);

            $refreshExpiresAtTimestamp = $refreshExpiresAt->timestamp;

        $now = Carbon::now()->timestamp;

        /** @var \Modules\ApplicationAuth\Entities\User $model */
        $model = $this->config->get('application-auth.models.user', User::class);
        $user = $model::findOrFail($payload->get('sub'));

        /** @var \Modules\ApplicationAuth\Transformers\UserResource $resource */
        $resource = $this->config->get('application-auth.resource', UserResource::class);

        return [
            'access_token' => $token,
            'expires_at' => $expiresAt,
            'expires_in' => $expiresAt - $now,
            'refresh_expires_at' => $refreshExpiresAtTimestamp,
            'refresh_expires_in' => $refreshExpiresAtTimestamp - $now,
            'user' => $resource::make($user)
                ->resolve(),
        ];
    }

    /**
     * Extract payload from token so we can use it.
     *
     * @param \Tymon\JWTAuth\Token|string $token
     * @param bool $isRefresh
     * @return \Tymon\JWTAuth\Payload|null
     */
    protected function getPayload($token, bool $isRefresh = false): ?Payload
    {
        try {
            $token = is_string($token) ? new Token($token) : $token;

            if (!($token instanceof Token)) {
                throw new \InvalidArgumentException("Expected token to be a string or an instance of " . Token::class);
            }

            return $this->jwt
                ->setRefreshFlow($isRefresh)
                ->decode($token, $isRefresh);
        } catch (JWTException $e) {
            return null;
        } finally {
            // Make sure to reset the refresh flow.
            $this->jwt->setRefreshFlow(false);
        }
    }

    /**
     * @return string|null
     */
    protected function getTokenId(): ?string
    {
        try {
            $payload = $this->guard->getPayload();

            if ($payload) {
                return $payload->get('jti');
            }
        } catch (\Throwable $t) {
        }

        return null;
    }


    /**
     * @param bool $reset
     */
    protected function setTokenRefresh(bool $reset = false): void
    {
        if ($reset) {
            // Make sure to reset the refresh ttl back so it won't cause problems if some other part of the application
            // uses these singletons.
            $this->blacklist->setRefreshTTL($this->refreshTtl);
            $this->validator->setRefreshTTL($this->refreshTtl);

            return;
        }

        if (($token = $this->guard->getToken()) &&
            ($payload = $this->getPayload($token, true)) &&
            $payload->get($this->getGuestClaimName())) {
            // Set the refresh TTL differently, so we can have a practically infinite token.
            $this->validator->setRefreshTTL($this->guestRefreshTtl);

            // Make sure to blacklist the token for at least as long as the token can be refreshed.
            $this->blacklist->setRefreshTTL($this->guestRefreshTtl);
        }
    }


    /**
     * @param \Illuminate\Contracts\Auth\Factory $auth
     */
    protected function setupMiddleware(Auth $auth): void
    {
        $this->middleware('auth:' . $this->guardName)
            ->only('logout', 'changePassword');

        $this->middleware(
            function (Request $request, \Closure $next) use ($auth) {
                $auth->shouldUse($this->guardName);

                return $next($request);
            }
        )->except('logout', 'changePassword', 'register');
    }
}

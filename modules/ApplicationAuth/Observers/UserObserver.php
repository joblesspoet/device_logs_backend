<?php

namespace Modules\ApplicationAuth\Observers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Jdenticon\Identicon;
use Modules\ApplicationAuth\Entities\User;

class UserObserver
{
    /** @var \Illuminate\Contracts\Hashing\Hasher */
    private Hasher $hasher;

    /**
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     */
    public function __construct(Hasher $hasher)
    {
        $this->hasher = $hasher;
    }

    
    /**
     * @param \Modules\ApplicationAuth\Entities\User $user
     * @return void
     */
    public function updated(User $user): void
    {
        if ($user->isDirty('email') &&
            !$user->isDirty('email_verified_at') &&
            !$user->isDirty('verify_email_token')) {
            $user->email_verified_at = null;

            if ($user instanceof MustVerifyEmail) {
                $user->sendEmailVerificationNotification();
            }
        }
    }
}

<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait GetAuthUserTrait
{
    /**
     * @return \App\User
     */
    protected function getAuthUser()
    {
        /** @var \App\User $user */
        $user = Auth::guard('sanctum')->user();

        return $user;
    }
}

<?php

namespace App\Http\Controllers\API\Traits;

trait GenerateToken
{
    /**
     * @return \App\User
     */
    protected function getToken()
    {
        return str_pad(random_int(1, 99999999), 8, '0');
    }
}

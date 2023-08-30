<?php

namespace Backpack\CRUD\app\Http\Requests;

use Illuminate\Foundation\Auth\EmailVerificationRequest as OriginalEmailVerificationRequest;
use Illuminate\Support\Facades\Cookie;

class EmailVerificationRequest extends OriginalEmailVerificationRequest
{
    public function user($guard = null)
    {
        return parent::user(backpack_guard_name()) ?? $this->getUserFromCookie();
    }

    private function getUserFromCookie()
    {
        if (Cookie::has('backpack_email_verification')) {
            return config('backpack.base.user_model_fqn')::where('email', Cookie::get('backpack_email_verification'))->first();
        }

        return null;
    }
}

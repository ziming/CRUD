<?php

namespace Backpack\CRUD\app\Http\Controllers\Auth;

use Backpack\CRUD\app\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Prologue\Alerts\Facades\Alert;

class VerifyEmailController extends Controller
{
    public null|string $redirectTo = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(backpack_middleware());
        $this->middleware('signed')->only('verifyEmail');
        $this->middleware('throttle:'.config('backpack.base.email_verification_throttle_access'))->only('resendVerificationEmail');

        if (! backpack_users_have_email()) {
            abort(500, trans('backpack::base.no_email_column'));
        }
        // where to redirect after the email is verified
        $this->redirectTo = $this->redirectTo !== null ? $this->redirectTo : backpack_url('dashboard');
    }

    public function emailVerificationRequired(): \Illuminate\Contracts\View\View
    {
        return view(backpack_view('auth.verify-email'));
    }

    /**
     * Verify the user's email address.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect($this->redirectTo);
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerificationEmail(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user(backpack_guard_name())->sendEmailVerificationNotification();

        Alert::success('Email verification link sent successfully.')->flash();

        return back()->with('status', 'verification-link-sent');
    }
}

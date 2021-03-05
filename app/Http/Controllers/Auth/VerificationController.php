<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // delete: ログイン済みチェックのため、ログイン不要でチェックしたいため、コメントアウト
        // $this->middleware('auth');
        $this->middleware('signed')->only('verify');

        // [TODO] 確認・再送6回でどうなるか確認
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the authenticated user's email address as verified.
     * Copy to OverWrite Illuminate\Foundation\Auth\VerifiesEmails::verify() (trait)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(Request $request)
    {
        // ユーザが存在しなければスロー
        $user = User::where('id', (string) $request->route('id'))->first();
        if (empty($user)) {
            // [TODO] スロー時の画面どうする？
            dd($request->route('id'));
            throw new AuthorizationException;
        }

        // if (! hash_equals((string) $request->route('id'), (string) $request->user()->getKey())) {
        if (! hash_equals((string) $request->route('id'), (string) $user->getKey())) {
            dd('verify2');
            throw new AuthorizationException;
        }

        // if (! hash_equals((string) $request->route('hash'), sha1($request->user()->getEmailForVerification()))) {
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            dd('verify3');
            throw new AuthorizationException;
        }

        // if ($request->user()->hasVerifiedEmail()) {
        if ($user->hasVerifiedEmail()) {
            session()->flash('flash_message_for_header', '既に認証済みです。登録したログインID、パスワードでログインしてください。');
            return redirect($this->redirectPath());
        }

        // if ($request->user()->markEmailAsVerified()) {
        //     event(new Verified($request->user()));
        // }
        if ($user->markEmailAsVerified()) {
            session()->flash('flash_message_for_header', '認証しました。登録したログインID、パスワードでログインしてください。');
            event(new Verified($user));
        }

        return redirect($this->redirectPath())->with('verified', true);
    }
}

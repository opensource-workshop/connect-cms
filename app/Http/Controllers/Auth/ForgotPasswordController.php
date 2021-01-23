<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

use App\Models\Core\Configs;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // パスワードリセットの使用
        $base_login_password_reset = Configs::where('name', 'base_login_password_reset')->first();

        if (empty($base_login_password_reset) || $base_login_password_reset->value == '0') {
            abort(403, "パスワードリセットを使用しないため、表示できません。");
        }

        $this->middleware('guest');
    }
}

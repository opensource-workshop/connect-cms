<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

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
        // move: パスワードリセットOFFで php artisan route:list コマンドを実行すると ここでabortしてエラー停止するため、パスワードリセットの使用チェックを app\Http\Middleware\ConnectForgotPassword.php に移動
        // // パスワードリセットの使用
        // $base_login_password_reset = Configs::where('name', 'base_login_password_reset')->first();

        // if (empty($base_login_password_reset) || $base_login_password_reset->value == '0') {
        //     // abort(403, "パスワードリセットを使用しないため、表示できません。");
        // }

        // パスワードリセットの使用
        $this->middleware('connect.forgot.password');

        $this->middleware('guest');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Enums\UserStatus;
use App\User;

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

    /**
     * パスワードリセットリンク送信に利用する資格情報を制限
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return array_merge(
            $request->only('email'),
            ['status' => UserStatus::active]
        );
    }

    /**
     * パスワードリセットリンク送信成功時のレスポンスを共通化
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        Log::info('Password reset link requested', $this->buildLogContext($request, $response));

        return $this->genericResetLinkResponse($request);
    }

    /**
     * パスワードリセットリンク送信失敗時も同一レスポンスとし、詳細はログへ
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        Log::warning('Password reset link request failed', $this->buildLogContext($request, $response));

        return $this->genericResetLinkResponse($request);
    }

    /**
     * 常に同一メッセージでレスポンスを返却
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    private function genericResetLinkResponse(Request $request)
    {
        $message = trans('passwords.sent');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message], 200);
        }

        return back()->with('status', $message);
    }

    /**
     * ログに出力する共通コンテキストを構築
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return array
     */
    private function buildLogContext(Request $request, $response): array
    {
        $context = [
            'email_hash' => $this->fingerprintEmail($request->input('email')),
            'response' => $response,
        ];

        if ($userId = $this->resolveUserId($request->input('email'))) {
            $context['user_id'] = $userId;
        }

        return $context;
    }

    /**
     * メールアドレスの不可逆ハッシュ値を生成
     *
     * @param  string|null  $email
     * @return string|null
     */
    private function fingerprintEmail($email): ?string
    {
        if (! is_string($email) || $email === '') {
            return null;
        }

        return hash('sha256', Str::lower(trim($email)));
    }

    /**
     * メールアドレスに紐づくユーザーIDを取得
     *
     * @param  string|null  $email
     * @return int|null
     */
    private function resolveUserId($email): ?int
    {
        if (! is_string($email) || $email === '') {
            return null;
        }

        $user_id = User::where('email', $email)->value('id');

        return $user_id !== null ? (int) $user_id : null;
    }
}

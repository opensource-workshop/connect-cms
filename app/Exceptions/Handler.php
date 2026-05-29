<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {

        // エラー処理
/*
        if (!$this->isHttpException($exception)) {

            Log::debug("-------------");
            Log::debug("--- Error ---");
            Log::debug("-------------");
            Log::debug('--- $_SERVER');
            Log::debug($_SERVER);
            Log::debug('--- $_REQUEST');
            Log::debug($_REQUEST);
            Log::debug('--- $exception');
            Log::debug($exception);

            abort(500);
        }
*/
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            // CSRFトークン有効期限切れ(419エラー)
            session()->flash('flash_message_for_header_class', 'alert-warning');
            session()->flash('flash_message_for_header', 'トークンの有効期限が切れたため、画面を再表示しました。');

            if ($request->has('redirect_path')) {
                // 一般プラグイン編集時リダイレクト対応
                return redirect($request->redirect_path)->withInput();
            }
            return redirect()->back()->withInput();
        }
        return parent::render($request, $exception);
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @link https://readouble.com/laravel/10.x/ja/errors.html#rendering-exceptions
     */
    public function register(): void
    {
        $this->renderable(function (\Swift_TransportException $e, $request) {
            // メール設定エラー
            return response()->view('errors.mail_setting_error', ['exception' => $e], 500);
        });
    }
}

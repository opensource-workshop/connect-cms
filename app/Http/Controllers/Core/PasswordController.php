<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

use DB;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;

use App\Traits\ConnectCommonTrait;

/**
 * 閲覧パスワードありの処理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
class PasswordController extends ConnectController
{

    use ConnectCommonTrait;

    /**
     *  処理の振り分け
     */
    public function invoke(Request $request, $action, $page_id)
    {
        // action チェック
        if ($action != 'input' && $action != 'auth') {
            // 何もせずに戻る。
            return;
        }

        // 該当の処理を呼ぶ。
        return $this->$action($request, $page_id);
    }

    /**
     *  パスワード入力画面の表示
     */
    public function input($request, $page_id)
    {
        return $this->view('auth.page_auth', [
            'page'    => $this->page,
            'page_id' => $page_id,
        ]);
    }

    /**
     *  パスワードチェック処理
     */
    public function auth($request, $page_id)
    {
        if (!$this->page) {
            // ページがなければチェック失敗
            return false;
        }

        // パスワードの照合
        if (!$this->page->checkPassword($request->password, $this->page_tree)) {

            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('password', 'パスワードが異なります。');

            return $this->input($request, $page_id)->withErrors($validator);
        }

        // セッションへの認証情報保持
        // 自分から先祖を遡って、最初にパスワードが設定されているページで認証するので、
        // セッションの保存もそのページで行う。
        $page_tree = $this->getAncestorsAndSelf($page_id);
        foreach ($page_tree as $page) {
            if (!empty($page->password)) {
                $request->session()->put('page_auth.'.$page->id, 'authed');
                break;
            }
        }

        // 本来表示したかったページへリダイレクト
        return redirect($this->page->permanent_link);
    }
}

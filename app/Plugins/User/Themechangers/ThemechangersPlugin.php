<?php

namespace App\Plugins\User\Themechangers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use App\Models\Core\Configs;
use App\Models\Common\Page;

use App\Plugins\User\UserPluginBase;

/**
 * テーマチェンジャー・プラグイン
 *
 * テーマ切り替えのテストのためのプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category テーマチェンジャー・プラグイン
 * @package Controller
 */
class ThemechangersPlugin extends UserPluginBase
{

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['select'];
        $functions['post'] = ['select'];
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 標準権限以外で設定画面などから呼ばれる権限の定義
        // 標準権限は右記で定義 config/cc_role.php
        //
        // 権限チェックテーブル (追加チェックなし)
        $role_check_table = [];
        return $role_check_table;
    }

    /**
     *  ページのカラム取得
     */
    private function getPagesColum($col_name)
    {
        // 自分のページから親を遡って取得
        $page_tree = Page::reversed()->ancestorsAndSelf($this->page->id);
        foreach ($page_tree as $page) {
            if (isset($page[$col_name])) {
                return $page[$col_name];
            }
        }
        return null;
    }

    /**
     *  指定されたテーマにCSS、JS があるか確認
     */
    private function checkAsset($theme, $theme_setting_array)
    {
        // CSS 存在チェック
        if (File::exists(public_path().'/themes/'.$theme.'/themes.css')) {
            $theme_setting_array['css'] = $theme;
        }

        // JS 存在チェック
        if (File::exists(public_path().'/themes/'.$theme.'/themes.js')) {
            $theme_setting_array['js'] = $theme;
        }

        return $theme_setting_array;
    }

    /**
     *  テーマ取得
     *  配列で返却['css' => 'テーマ名', 'js' => 'テーマ名']
     *  値がなければキーのみで値は空
     */
    private function getPageThemes($request = null)
    {
        // 戻り値
        $return_array = array('css' => '', 'js' => '');

        // セッションにテーマの選択がある場合（テーマ・チェンジャーで選択時の動き）
        if ($request && $request->session()->get('session_theme')) {
            return  $this->checkAsset($request->session()->get('session_theme'), $return_array);
        }

        // ページ固有の設定がある場合
        $theme = $this->getPagesColum('theme');
        if ($theme) {
            // CSS、JS をチェックして配列にして返却
            return  $this->checkAsset($theme, $return_array);
        }
        // テーマが設定されていない場合は一般設定の取得
        $configs = Configs::where('name', 'base_theme')->first();

        // CSS、JS をチェックして配列にして返却
        return  $this->checkAsset($configs->value, $return_array);
    }

    /**
     *  初期表示取得関数
     *
     * @return view
     */
    public function index($request, $page_id, $frame_id)
    {
        // テーマ取得
        $page_theme = $this->getPageThemes($request);

        // テーマ選択肢の取得
        $themes = $this->getThemes();

        // セッションに背景を黒の指定があるか
        if ($request->session()->get('session_header_black') == true) {
            $session_header_black = true;
        } else {
            $session_header_black = false;
        }

        // 画面へ
        return $this->view('themechangers', [
            'page_theme'           => $page_theme,
            'themes'               => $themes,
            'session_header_black' => $session_header_black,
        ]);
    }

    /**
     *  テーマ選択関数
     */
    public function select($request, $page_id, $frame_id)
    {
        // テーマの選択を元に戻す。
        if ($request->session_theme == 'session:clear') {
            $request->session()->forget('session_theme');
            return;
        }

        // 選択したテーマをセッションに保持する。
        $request->session()->put('session_theme', $request->session_theme);

        // 背景の黒チェックをセッションに保持する。
        if ($request->has('session_header_black') && $request->session_header_black == '1') {
            $request->session()->put('session_header_black', true);
        } else {
            $request->session()->put('session_header_black', false);
        }

        // /redirect/plugin/themechangers で呼ばれる想定なので、この後、リダイレクトされる。
    }
}

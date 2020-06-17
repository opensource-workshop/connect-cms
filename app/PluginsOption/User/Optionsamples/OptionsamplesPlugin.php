<?php

namespace App\PluginsOption\User\Optionsamples;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;
use Session;

use App\Models\Core\Configs;
use App\Models\Common\Buckets;
use App\Models\Common\Categories;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\ModelsOption\User\Optionsamples\Optionsamples;

use App\PluginsOption\User\UserPluginOptionBase;

/**
 * オプション・プラグインのサンプル
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category オプション・プラグインのサンプル
 * @package Contoroller
 */
class OptionsamplesPlugin extends UserPluginOptionBase
{

    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
        ];
        $functions['post'] = [
        ];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        //$role_ckeck_table[""]                = array('');
        return $role_ckeck_table;
    }

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // 表示テンプレートを呼び出す。
        return $this->view(
            'optionsamples', [
            'optionsamples_test' => '変数のテスト',
            ]
        );
    }
}

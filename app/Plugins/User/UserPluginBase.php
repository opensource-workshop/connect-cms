<?php

namespace App\Plugins\User;

use App\Plugins\PluginBase;

use App\Frame;

/**
 * ユーザープラグイン
 *
 * ユーザ用プラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Contoroller
 */
class UserPluginBase extends PluginBase
{

    /**
     *  フレームオブジェクト
     */
    public $frame = null;

    /**
     *  コンストラクタ
     */
    function __construct($frame)
    {
        // フレームの保持
        //$this->frame = Frame::where('id', $frame->frame_id)->first();
        $this->frame = $frame;
        //print_r($this->frame);
    }

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invoke($obj, $request, $action, $page_id, $frame_id, $id)
    {
        // 画面(コアの cms_frame)で指定されたクラスのアクションのメソッドを呼び出す。
        // 戻り値は各アクションでのメソッドでview 関数などで生成したHTML なので、そのままreturn して元の画面に戻す。
        return $obj->$action($request, $page_id, $frame_id, $id);
    }

    /**
     *  View のパス
     *
     * @return view
     */
    public function getViewPath($blade_name)
    {
        return 'plugins.user.' . $this->frame->plugin_name . '.' . $this->frame->template . '.' . $blade_name;
    }

    /**
     *  テンプレート
     *
     * @return view
     */
    public function getTemplate()
    {
        return $this->frame->template;
    }
}

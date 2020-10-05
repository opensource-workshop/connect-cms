<?php

namespace App\Plugins\User\Conventions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\User;

/**
 * イベント管理のツール群
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category イベント管理プラグイン
 * @package Contoroller
 */
class ConventionsTool
{
    /**
     * イベント・基本データ
     */
    private $convention = null;

    /**
     * イベント・コマデータ
     */
    private $posts = null;

    /**
     * 参加データ
     */
    private $joins = null;

    /**
     * ユーザデータ
     */
    private $user = null;

    /**

     * コンストラクタ
     */
    public function __construct($request, $page_id, $frame_id, $convention, $posts, $joins = null)
    {
        // 変数初期化
        $this->convention = $convention;
        $this->posts = $posts;
        $this->joins = $joins;

        // ログインしているユーザ
        $this->user = Auth::user();
    }

    /**
     *  コマ特定
     */
    private function getPeriod($track, $period)
    {
        return $this->posts->where('track', $track)->where('period', $period)->first();
    }

    /**
     *  コマの存在判定
     */
    public function hasPeriod($track, $period)
    {
        $period = $this->getPeriod($track, $period);
        if ($period) {
            return true;
        }
        return false;
    }

    /**
     *  post_id 取得
     */
    public function getPostId($track, $period)
    {
        $period = $this->getPeriod($track, $period);
        if ($period) {
            return $period->id;
        }
        return null;
    }

    /**
     *  タイトル取得
     */
    public function getTitle($track, $period, $default = '')
    {
        $period = $this->getPeriod($track, $period);
        if ($period) {
            return $period->title;
        }
        return $default;
    }

    /**
     *  詳細説明取得
     */
    public function getDescription($track, $period, $default = '')
    {
        $period = $this->getPeriod($track, $period);
        if ($period) {
            return "<p>" . $period->description . "</p>";
        }
        return $default;
    }

    /**
     *  リンクタグ取得
     */
    public function getLinkTag($track, $period, $default = '')
    {
        $period = $this->getPeriod($track, $period);
        if ($period) {
            return "<a href=\"" . $period->url . "\" target=\"_blank\">" . $period->url . "</a>";
        }
        return $default;
    }

    /**
     *  コマ説明取得
     */
    public function getPeriodLabel($period_no, $default = '')
    {
        if (empty($this->convention)) {
            return "";
        }
        $period_labels = explode(',', $this->convention->period_label);
        if (array_key_exists($period_no - 1, $period_labels)) {
            return $period_labels[$period_no - 1];
        }

        return $default;
    }

    /**
     *  参加しているか判定
     */
    public function isJoin($track, $period)
    {
        if (empty($this->joins)) {
            return false;
        }
        $period = $this->getPeriod($track, $period);
        if (empty($period)) {
            return false;
        }

        $join = $this->joins->where('post_id', $period->id)->first();
        if (empty($join)) {
            return false;
        }

        if ($join->join_flag == 1) {
            return true;
        }
        return false;
    }
}

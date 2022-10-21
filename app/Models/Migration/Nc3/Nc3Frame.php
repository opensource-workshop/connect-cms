<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3Frame extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'frames';

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = ['created', 'modified'];

    /**
     * NC3 header_type -> Connect-CMS frame_design 変換用テーブル
     * 定義のないものは 'default' になる想定
     */
    protected $frame_designs = [
        'none'          => 'none',
        'default'       => 'default',
        'primary'       => 'primary',
        'success'       => 'success',
        'info'          => 'info',
        'warning'       => 'warning',
        'danger'        => 'danger',
    ];

    /**
     *  フレームテンプレートの変換
     */
    public function getFrameDesign($default = 'default')
    {
        // NC3 テンプレート変換配列にあれば、その値。
        // なければ default を返す。
        if (array_key_exists($this->header_type, $this->frame_designs)) {
            return $this->frame_designs[$this->header_type];
        }
        return $default;
    }
}

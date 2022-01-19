<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Kalnoy\Nestedset\NodeTrait;

class Dusks extends Model
{
    // 入れ子集合モデル
    use NodeTrait;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'category', 'sort',
        'plugin_name', 'plugin_title', 'plugin_desc',
        'method_name', 'method_title', 'method_desc', 'method_detail',
        'html_path', 'img_paths', 'test_result',
    ];

    /**
     * マニュアル用のデータの受け取り
     */
    public function setMethodManual($manual_docs)
    {
        $method_doc = $manual_docs[$this->method_name];
        $this->method_title  = $method_doc['title'];
        $this->method_desc   = $method_doc['desc'];
        $this->method_detail = $method_doc['detail'];
    }

    /**
     * 画像パスの配列
     */
    public function getImgPathArray()
    {
        if (json_decode($this->img_paths)) {
            $json_paths = json_decode($this->img_paths);
            $ret = array();
            foreach ($json_paths as $json_path) {
                $ret[] = $json_path->name;
            }
            return $ret;
        } else {
            return explode(',', $this->img_paths);
        }
    }

    /**
     * html_path を取得
     *
     * @return string
     */
    public function getHtmlPathAttribute()
    {
        if (empty($this->id)) {
            return "index.html";
        }
        return $this->attributes['html_path'];
    }
}

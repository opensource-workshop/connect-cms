<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class ApiSecret extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['secret_name', 'secret_code', 'apis', 'ip_address'];

    /**
     * 使用するAPI の配列を返す
     */
    public function getApiCheckbpoxs($api_inis, $check_off = false)
    {
        $apis = explode(',', $this->apis);
        $ret_apis = array();
        foreach ($api_inis as $api_name => $api_ini) {
            $check = false;
            if (!$check_off && in_array($api_name, $apis)) {
                $check = true;
            }
            $ret_apis[$api_name]['check'] = $check;
            $ret_apis[$api_name]['plugin_name_full'] = $api_ini['plugin_name_full'];
        }
        return $ret_apis;
    }
}

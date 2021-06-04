<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;

use App\UserableNohistory;

class FrameConfig extends Model
{
    use UserableNohistory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'frame_id',
        'name',
        'value'
    ];

    /**
     * フレーム設定の値を抜き出す
     *
     * @param Illuminate\Database\Eloquent\Collection $frame_configs フレーム設定
     * @param string $name 名称
     * @param bool|string $default 初期値
     * @return string 値
     */
    public static function getConfigValue(Collection $frame_configs, string $name, $default = '')
    {
        $value = $default;
        if (empty($frame_configs)) {
            return $value;
        }

        $config = $frame_configs->where('name', $name)->first();
        if (!empty($config)) {
            $value = $config->value;
        }

        return $value;
    }

    /**
     * フレーム設定の値取得. old対応あり
     */
    public static function getConfigValueAndOld(Collection $frame_configs, string $name, $default = '')
    {
        $value = self::getConfigValue($frame_configs, $name, $default);

        // oldの値があれば、その値を使う
        $value = old($name, $value);
        return $value;
    }
}

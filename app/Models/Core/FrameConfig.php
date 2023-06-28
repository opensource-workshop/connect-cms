<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

class FrameConfig extends Model
{
    use SoftDeletes, UserableNohistory;

    /**
     * @var string
     */
    const CHECKBOX_SEPARATOR = '|';

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

    /**
     * フレーム設定を保存する。
     *
     * @param Illuminate\Http\Request $request リクエスト
     * @param int $frame_id フレームID
     * @param array $frame_config_names フレーム設定のname配列
     * @param string $checkbox_separator チェックボックスの区切り文字
     */
    public static function saveFrameConfigs(\Illuminate\Http\Request $request, int $frame_id, array $frame_config_names, ?string $checkbox_separator = self::CHECKBOX_SEPARATOR) : void
    {
        foreach ($frame_config_names as $key => $name) {

            $request_value = $request->$name;
            // arrayならarray_filter()でarrayの空要素削除
            $request_value = is_array($request_value) ? array_filter($request_value) : $request_value;

            // 必須入力チェック
            // チェックボックスの場合、すべて選択しない場合があるので除外する
            if (!is_array($request_value) && $request_value != '0' && empty($request_value)) {
                continue;
            }

            // 配列の設定値はパイプ区切りにする
            $value = is_array($request_value) ? implode($checkbox_separator, $request_value) : $request_value;

            self::updateOrCreate(
                ['frame_id' => $frame_id, 'name' => $name],
                ['value' => $value]
            );
        }
    }
}

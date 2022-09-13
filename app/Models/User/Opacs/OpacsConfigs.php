<?php

namespace App\Models\User\Opacs;

use Illuminate\Database\Eloquent\Model;

class OpacsConfigs extends Model
{
    // 更新する項目の定義
    protected $fillable = ['opacs_id', 'name', 'value'];

    /**
     * Opac設定のためのデータ取得
     */
    public static function getConfigs($opac_id, $original_roles): array
    {
        // Opac設定で必要な形を生成
        // キーを作成、初期値を入れておく。これを画面に渡すことで、null エラーを回避
        $opac_configs = [];
        $opac_configs['lent_days_global'] = 0;
        foreach ($original_roles as $original_role) {
            $opac_configs['lent_days_'.$original_role->name] = 0;
        }
        $opac_configs['lent_limit_global'] = 0;
        foreach ($original_roles as $original_role) {
            $opac_configs['lent_limit_'.$original_role->name] = 0;
        }
        $opac_configs['rule_date_after_equal'] = null;
        $opac_configs['rule_date_before_equal'] = null;
        $opac_configs['delivery_request_date_caption'] = null;

        // 個別登録データを反映
        $opacs_configs = self::where('opacs_id', $opac_id)->get();
        foreach ($opacs_configs as $opacs_config) {
            $opac_configs[$opacs_config->name] = $opacs_config->value;
        }

        return $opac_configs;
    }
}

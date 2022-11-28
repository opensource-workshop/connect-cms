<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Nc3BlockSetting extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'block_settings';

    /**
     * block_settingsのvalueをblock_key,field_nameで取得
     */
    public static function getNc3BlockSettingValue(Collection $block_settings, ?string $block_key, string $field_name, ?string $default = '0'): string
    {
        $block_setting = $block_settings->where('block_key', $block_key)
            ->firstWhere('field_name', $field_name);
        $block_setting = $block_setting ?? new Nc3BlockSetting();
        return $block_setting->value ?? $default;
    }
}

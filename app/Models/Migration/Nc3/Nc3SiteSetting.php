<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Nc3SiteSetting extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'site_settings';

    /**
     * site_settingsのvalueをkeyで取得
     */
    public static function getNc3SiteSettingValueByKey(Collection $site_settings, string $key): ?string
    {
        $site_setting = $site_settings->firstWhere('key', $key);
        $site_setting = $site_setting ?? new Nc3SiteSetting();
        return $site_setting->value;
    }
}

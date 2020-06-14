<?php

namespace App\Models\User\Covids;

use Illuminate\Database\Eloquent\Model;

use App\Userable;

class CovidDailyReport extends Model
{
    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['covid_id',
                           'target_date',
                           'fips',
                           'admin2',
                           'province_state',
                           'country_region',
                           'last_update',
                           'lat',
                           'long_',
                           'confirmed',
                           'deaths',
                           'recovered',
                           'active',
                           'combined_key',
                           'incidence_rate',
                           'case_fatality_ratio',
                          ];
}

<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3AccessCounterFrameSetting extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'access_counter_frame_settings';

    /**
     * 表示タイプ
     *
     * @var string
     */
    const display_type_default = '1',
        display_type_primary = '2',
        display_type_success = '3',
        display_type_info = '4',
        display_type_warning = '5',
        display_type_danger = '6';
}

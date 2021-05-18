<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;

class FrameConfig extends Model
{

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'frame_id',
        'name',
        'value'
    ];

    /**
     *
     */
    public function getConfigValues(Collection $frame_configs , int $frame_id, string $name)
    {
        $configs = [];
        $frame_configs->where('frame_id', $frame_id)
            ->where('name', $name)
            ->each(function ($item, $key) use (&$configs){
                $configs[] = $item->value;
        });

        return $configs;
    }

    public static function getConfigs(Collection $frame_configs , int $frame_id)
    {
        $configs = [];
        $frame_configs->where('frame_id', $frame_id)
            ->each(function ($item, $key) use (&$configs){
                $configs[$item->name][] = $item->value;
        });

        return $configs;
    }

}

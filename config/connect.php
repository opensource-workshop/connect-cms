<?php

return [

    // 画像がなかった場合の「no image」
    'no_image_path' => 'app/uploads/default/no_image.png',

    // プラグイン管理にも表示しないプラグイン(小文字で指定)
    'PLUGIN_FORCE_HIDDEN' => ['sampleforms', 'knowledges', 'codestudies', 'opacs'],
    //'PLUGIN_FORCE_HIDDEN' => ['sampleforms', 'knowledges', 'codestudies'],

    // 特別なPath定義(管理画面)
    'CC_SPECIAL_PATH_MANAGE' => array_merge(
        ['manage' => [
            'plugin' => 'App\Plugins\Manage\IndexManage\IndexManage',
            'method' => 'index',
            'page_id' => null,
            'flame_id' => null,
        ]]
    ),

    // 特別なPath定義(一般画面)
    'CC_SPECIAL_PATH' => array_merge(
        json_decode(env('CC_SPECIAL_PATH', '{}'), true)
    ),
];

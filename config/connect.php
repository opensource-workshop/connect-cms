<?php

return [

    // 画像がなかった場合の「no image」
    'no_image_path' => 'app/public/no_image.png',

    // 画像に権限がなかった場合の「forbidden」
    'forbidden_image_path' => 'app/public/forbidden.png',

    // uploads ディレクトリのベース・ディレクトリ
    'directory_base' => "uploads/",

    // uploads ディレクトリの1ディレクトリの最大ファイル数
    'directory_file_limit' => 1000,

    // プラグイン管理にも表示しないプラグイン(小文字で指定)
    'PLUGIN_FORCE_HIDDEN' => ['sampleforms', 'knowledges', 'codestudies'],

    // 特別なPath定義(管理画面)
    'CC_SPECIAL_PATH_MANAGE' => array_merge(
        ['manage' => [
            'plugin' => 'App\Plugins\Manage\IndexManage\IndexManage',
            'method' => 'index',
            'page_id' => null,
            'flame_id' => null,
        ]]
    ),

    // 特別なPath定義(マイページ画面)
    'CC_SPECIAL_PATH_MYPAGE' => array_merge(
        ['mypage' => [
            'plugin' => 'App\Plugins\Mypage\IndexMypage\IndexMypage',
            'method' => 'index',
            'page_id' => null,
            'flame_id' => null,
        ]]
    ),

    // 特別なPath定義(一般画面)
    'CC_SPECIAL_PATH' => array_merge(
        json_decode(env('CC_SPECIAL_PATH', '{}'), true)
    ),

    // 新着の表示制限(新着に表示しない。)の対象プラグイン
    'CC_DISABLE_WHATSNEWS_PLUGIN' => array(
        'blogs' => true,
    ),

    // データがない場合にフレームに表示する対象のプラグイン
    'CC_NONE_HIDDEN_PLUGIN' => array(
        'whatsnews' => true,
    ),

    // 設定メニューの折り畳みcol
    'CC_SETTING_EXPAND_COL' => 6,

    // ダウンロード時にカウントする拡張子
    'CC_COUNT_EXTENSION' => array('pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'sb2', 'sb3'),

    // OSWS 翻訳サービス使用の有無
    'OSWS_TRANSLATE_AGREEMENT' => env('OSWS_TRANSLATE_AGREEMENT', false),

    // Cache-Control
    'CACHE_CONTROL' => env('CACHE_CONTROL', 'no-store'),

    // Expires
    'EXPIRES' => env('EXPIRES', 'Thu, 01 Dec 1994 16:00:00 GMT'),

    // Login link path
    'LOGIN_PATH' => env('LOGIN_PATH', 'login'),

    // Self register base role.(comma separator. Not set is guest)
    'SELF_REGISTER_BASE_ROLES' => env('SELF_REGISTER_BASE_ROLES', ''),

    // Custom message.
    'cc_lang_ja_messages_search_results_empty' => env('cc_lang_ja_messages_search_results_empty'),
    'cc_lang_ja_messages_enter_same_email' => env('cc_lang_ja_messages_enter_same_email'),
    'cc_lang_ja_messages_input_user_name' => env('cc_lang_ja_messages_input_user_name'),
];

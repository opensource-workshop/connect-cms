<?php

return [

    // 画像がなかった場合の「no image」
    'no_image_path' => 'app/public/no_image.png',

    // 画像に権限がなかった場合の「forbidden」
    'forbidden_image_path' => 'app/public/forbidden.png',

    // uploads ディレクトリのベース・ディレクトリ
    'directory_base' => env('UPLOADS_DIRECTORY_BASE', "uploads/"),

    // uploads ディレクトリの1ディレクトリの最大ファイル数
    'directory_file_limit' => 1000,

    // マニュアル生成のベース・ディレクトリ
    'manual_put_base' => env('MANUAL_PUT_BASE', ''),

    // マニュアルの問合せ先ページ内容
    'manual_contact_page' => env('MANUAL_CONTACT_PAGE', ''),

    // マニュアル生成時の声
    'manual_voiceid' => env('MANUAL_VOICEID', 'takumi'),

    // プラグイン管理にも表示しないプラグイン(小文字で指定)
    'PLUGIN_FORCE_HIDDEN' => ['knowledges', 'codestudies'],

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

    // Cache-Control
    'CACHE_CONTROL' => env('CACHE_CONTROL', 'max-age=604800'),

    // Login link path
    'LOGIN_PATH' => env('LOGIN_PATH', 'login'),
    // Login link string
    'LOGIN_STR' => env('LOGIN_STR', 'ログイン'),

    // Self register base role.(comma separator. Not set is guest)
    'SELF_REGISTER_BASE_ROLES' => env('SELF_REGISTER_BASE_ROLES', null),

    // Custom message.
    'cc_lang_ja_messages_search_results_empty'            => env('cc_lang_ja_messages_search_results_empty'),
    'cc_lang_ja_messages_enter_same_email'                => env('cc_lang_ja_messages_enter_same_email'),
    'cc_lang_ja_messages_input_user_name'                 => env('cc_lang_ja_messages_input_user_name'),
    'cc_lang_ja_messages_to_regist'                       => env('cc_lang_ja_messages_to_regist'),
    'cc_lang_ja_messages_regist_application'              => env('cc_lang_ja_messages_regist_application'),
    'cc_lang_ja_messages_regist_confirmed'                => env('cc_lang_ja_messages_regist_confirmed'),
    'cc_lang_ja_messages_change_application'              => env('cc_lang_ja_messages_change_application'),
    'cc_lang_ja_messages_change_confirmed'                => env('cc_lang_ja_messages_change_confirmed'),
    'cc_lang_ja_messages_confirm_of_regist_application'   => env('cc_lang_ja_messages_confirm_of_regist_application'),
    'cc_lang_ja_messages_confirmed_of_regist_application' => env('cc_lang_ja_messages_confirmed_of_regist_application'),
    'cc_lang_ja_messages_confirm_of_change_application'   => env('cc_lang_ja_messages_confirm_of_change_application'),
    'cc_lang_ja_messages_confirmed_of_change_application' => env('cc_lang_ja_messages_confirmed_of_change_application'),

    // csrfチェックの除外設定
    'VERIFY_CSRF_TOKEN_EXCEPT' => env('VERIFY_CSRF_TOKEN_EXCEPT', ''),

    // Slackの署名付きトークン
    'SLACK_SIGNING_SECRET' => env('SLACK_SIGNING_SECRET', ''),

    // 外部APIを使って翻訳
    'TRANSLATE_API_URL' => env('TRANSLATE_API_URL', ''),
    'TRANSLATE_API_KEY' => env('TRANSLATE_API_KEY', ''),

    // 外部APIを使ってPDFからサムネイルを自動作成
    'PDF_THUMBNAIL_API_URL' => env('PDF_THUMBNAIL_API_URL', ''),
    'PDF_THUMBNAIL_API_KEY' => env('PDF_THUMBNAIL_API_KEY', ''),

    // 外部APIを使ってPDFから文字列を抽出
    'PDF_TO_TEXT_API_URL' => env('PDF_TO_TEXT_API_URL', ''),
    'PDF_TO_TEXT_API_KEY' => env('PDF_TO_TEXT_API_KEY', ''),

    // 外部APIを使って顔認識処理
    'FACE_AI_API_URL' => env('FACE_AI_API_URL', ''),
    'FACE_AI_API_KEY' => env('FACE_AI_API_KEY', ''),
    'FACE_AI_DEFAULT_SIZE' => '1200',

    // 外部APIを使って音声合成処理
    'SPEECH_API_URL' => env('SPEECH_API_URL', ''),
    'SPEECH_API_KEY' => env('SPEECH_API_KEY', ''),

    // cURL オプション
    'HTTPPROXYTUNNEL' => env('HTTPPROXYTUNNEL', false),
    'PROXYPORT' => env('PROXYPORT', ''),
    'PROXY' => env('PROXY', ''),
    'PROXYUSERPWD' => env('PROXYUSERPWD', ''),
    'CURL_TIMEOUT' => env('CURL_TIMEOUT', ),

    // WYSIWYG のバイト数チェックの数値（MySQLのtext型）
    'WYSIWYG_MAX_BYTE' => 65535,

    // TEXT 型のバイト数チェックの数値（MySQLのtext型）
    'TEXT_MAX_BYTE' => 65535,

    // URL 項目のバイト数チェックの数値（MySQLのTEXT型。MySQL の「最大行サイズは 65,535 バイト」の制約対応。8190 バイトでチェック）
    'URL_MAX_BYTE' => 8190,

    // サムネイル サイズ
    'THUMBNAIL_SIZE' => [
        'SMALL' => 200,
        'MEDIUM' => 400,
        'LARGE' => 800,
    ],

    // キャッシュ保持時間（分）10080 は1週間（7日）
    'CACHE_MINUTS' => 10080,

    // AWS SDK
    'AWS' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'ap-northeast-1'),
    ],

    // FFMpeg path
    'FFMPEG_PATH' => env('FFMPEG_PATH'),

    // AWS SDK
    'REQUIRE_AWS_SDK_PATH' => env('REQUIRE_AWS_SDK_PATH'),

    // add theme dir
    'ADD_THEME_DIR' => env('ADD_THEME_DIR'),

    // Use the container (beta)
    'USE_CONTAINER_BETA' => env('USE_CONTAINER_BETA', false),

    // ユーザの項目セットを使う
    'USE_USERS_COLUMNS_SET' => env('USE_USERS_COLUMNS_SET', false),

    // QUEUE_CONNECTION=database 時に使われるPHP BINのパス. null時は自動判定
    'QUEUE_PHP_BIN' => env('QUEUE_PHP_BIN', null),

    // 連番管理の連番クリア機能を無効化するプラグイン名
    'PLUGIN_NAME_TO_DISABLE_SERIAL_NUMBER_CLEAR' => env('PLUGIN_NAME_TO_DISABLE_SERIAL_NUMBER_CLEAR', null),

    // データベースプラグイン
    // 詳細画面で非表示項目をパラメータのID指定で強制的に表示する機能(beta)
    'DATABASES_FORCE_SHOW_COLUMN_ON_DETAIL' => env('DATABASES_FORCE_SHOW_COLUMN_ON_DETAIL', false),
    // 絞り込み項目の登録済み件数を表示する(beta)
    'DATABASES_SHOW_SEARCH_COLUMN_COUNT' => env('DATABASES_SHOW_SEARCH_COLUMN_COUNT', false),

    // public配下のディレクトリを指定してファイル管理. null時は機能自体使わない(beta)
    'MANAGE_USERDIR_PUBLIC_TARGET' => env('MANAGE_USERDIR_PUBLIC_TARGET', null),

    // 契約ユーザの個別サポート情報
    'common_support_url' => env('COMMON_SUPPORT_URL', ""),
    'individual_support_url' => env('INDIVIDUAL_SUPPORT_URL', ""),
    'individual_support_password' => env('INDIVIDUAL_SUPPORT_PASSWORD', ""),
];

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

    // ダウンロード時にカウントする拡張子
    'CC_COUNT_EXTENSION' => array('pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'sb2', 'sb3', 'mp4'),

    // delete: TRANSLATE_API_URL, TRANSLATE_API_KEYに設定見直し
    // OSWS 翻訳サービス使用の有無
    // 'OSWS_TRANSLATE_AGREEMENT' => env('OSWS_TRANSLATE_AGREEMENT', false),

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

    // csrfチェックの除外設定
    'VERIFY_CSRF_TOKEN_EXCEPT' => env('VERIFY_CSRF_TOKEN_EXCEPT', ''),

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

    // cURL オプション
    'HTTPPROXYTUNNEL' => env('HTTPPROXYTUNNEL', false),
    'PROXYPORT' => env('PROXYPORT', ''),
    'PROXY' => env('PROXY', ''),
    'PROXYUSERPWD' => env('PROXYUSERPWD', ''),

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
];

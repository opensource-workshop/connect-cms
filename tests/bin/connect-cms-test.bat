@echo off
chcp 932

rem ----------------------------------------------
rem batでまとめてテスト実行
rem > tests\bin\connect-cms-test.bat
rem
rem > tests\bin\connect-cms-test.bat trancate  <<-- データのクリア＆シーダー
rem > tests\bin\connect-cms-test.bat fresh     <<-- テーブルの再構築＆シーダー
rem
rem マニュアル出力
rem > php artisan dusk tests\Manual\src\ManualOutput.php
rem > php artisan dusk tests\Manual\src\ManualPdf.php
rem
rem [How to test]
rem https://github.com/opensource-workshop/connect-cms/wiki/Dusk
rem ----------------------------------------------

if exist .env.dusk.local (
    echo .env.dusk.local で実行します。
) else (
    echo .env.dusk.local が存在しないため、テストを実行せずに終了します。
    exit /b
)

rem テストコマンド実行時に１度だけ、自動テストDB初期化をするので不要です。
rem   (see) https://github.com/opensource-workshop/connect-cms/wiki/Dusk#手動でテストdb初期化
rem @php artisan config:clear

if "%1" == "trancate" (
    rem 下記は、自動テストDB初期化で行っていないコマンド
    rem echo --- キャッシュクリア
    rem php artisan cache:clear
    rem php artisan config:clear

    echo --- データベース・クリア
    php artisan db:seed --env=dusk.local --class=TruncateAllTables

    echo --- データ・初期追加
    php artisan db:seed --env=dusk.local
)

if "%1" == "fresh" (
    rem 下記は、自動テストDB初期化で行っていないコマンド
    rem echo --- キャッシュクリア
    rem php artisan cache:clear
    rem php artisan config:clear

    echo --- テーブルの再構築
    php artisan migrate:fresh --env=dusk.local

    echo --- データ・初期追加
    php artisan db:seed --env=dusk.local
)

rem ---------------------------------------------
rem - 事前準備用の実行
rem ---------------------------------------------

echo --- データ準備用 - ログ管理 - マニュアルなし
php artisan dusk tests\Browser\Manage\LogManageTest.php no_manual

rem ---------------------------------------------
rem - 管理プラグイン
rem ---------------------------------------------

echo --- 管理画面アクセス
php artisan dusk tests\Browser\Manage\IndexManageTest.php

echo --- ページ管理のテスト
php artisan dusk tests\Browser\Manage\PageManageTest.php

echo --- サイト管理のテスト
php artisan dusk tests\Browser\Manage\SiteManageTest.php

echo --- ユーザ管理のテスト
php artisan dusk tests\Browser\Manage\UserManageTest.php

echo --- グループ管理のテスト
php artisan dusk tests\Browser\Manage\GroupManageTest.php

echo --- セキュリティ管理のテスト
php artisan dusk tests\Browser\Manage\SecurityManageTest.php

echo --- プラグイン管理のテスト
php artisan dusk tests\Browser\Manage\PluginManageTest.php

echo --- システム管理のテスト
php artisan dusk tests\Browser\Manage\SystemManageTest.php

echo --- API管理のテスト
php artisan dusk tests\Browser\Manage\ApiManageTest.php

echo --- メッセージ管理のテスト
php artisan dusk tests\Browser\Manage\MessageManageTest.php

echo --- 外部認証管理のテスト
php artisan dusk tests\Browser\Manage\AuthManageTest.php

echo --- 外部サービス設定のテスト
php artisan dusk tests\Browser\Manage\ServiceManageTest.php

rem ---------------------------------------------
rem - データ管理プラグイン
rem ---------------------------------------------

echo --- アップロードファイル
php artisan dusk tests\Browser\Manage\UploadfileManageTest.php

echo --- テーマ管理
php artisan dusk tests\Browser\Manage\ThemeManageTest.php

echo --- 連番管理
php artisan dusk tests\Browser\Manage\NumberManageTest.php

echo --- コード管理
php artisan dusk tests\Browser\Manage\CodeManageTest.php

echo --- ログ管理
php artisan dusk tests\Browser\Manage\LogManageTest.php

echo --- 祝日管理
php artisan dusk tests\Browser\Manage\HolidayManageTest.php

echo --- 他システム移行
php artisan dusk tests\Browser\Manage\MigrationManageTest.php

rem ---------------------------------------------
rem - コア
rem ---------------------------------------------

echo --- ページなし(404)
rem php artisan dusk tests\Browser\Core\PageNotFoundTest.php

echo --- 権限なし(403)
rem php artisan dusk tests\Browser\Core\PageForbiddenTest.php

echo --- 初回確認メッセージ動作テスト
rem php artisan dusk tests\Browser\Core\MessageFirstShowTest.php

echo --- 初回確認メッセージ動作テスト 項目フル入力
rem php artisan dusk tests\Browser\Core\MessageFirstShowFullTest.php

echo --- 閲覧パスワード付ページテスト
rem php artisan dusk tests\Browser\Core\PagePasswordTest.php

echo --- ログインテスト
rem php artisan dusk tests\Browser\Core\LoginTest.php

rem ---------------------------------------------
rem - 共通
rem ---------------------------------------------

echo --- ログイン・ログアウト
php artisan dusk tests\Browser\Common\LoginLogoutTest.php

echo --- 管理機能
php artisan dusk tests\Browser\Common\AdminLinkTest.php

rem ---------------------------------------------
rem - 一般プラグイン
rem ---------------------------------------------

echo --- 固定記事
php artisan dusk tests\Browser\User\ContentsPluginTest.php

echo --- メニュー
php artisan dusk tests\Browser\User\MenusPluginTest.php

echo --- ブログ
php artisan dusk tests\Browser\User\BlogsPluginTest.php

echo --- カレンダー
php artisan dusk tests\Browser\User\CalendarsPluginTest.php

echo --- スライドショー
php artisan dusk tests\Browser\User\SlideshowsPluginTest.php

echo --- 開館カレンダー
php artisan dusk tests\Browser\User\OpeningcalendarsPluginTest.php

echo --- 新着情報
php artisan dusk tests\Browser\User\WhatsnewsPluginTest.php

echo --- FAQ
php artisan dusk tests\Browser\User\FaqsPluginTest.php

echo --- リンクリスト
php artisan dusk tests\Browser\User\LinklistsPluginTest.php

echo --- キャビネット
php artisan dusk tests\Browser\User\CabinetsPluginTest.php

echo --- フォトアルバム
php artisan dusk tests\Browser\User\PhotoalbumsPluginTest.php

echo --- データベース
rem php artisan dusk tests\Browser\User\DatabasesPluginTest.php

echo ※ スクリーンショットの保存先
echo tests\Browser\screenshots

rem ---------------------------------------------
rem - マニュアル
rem ---------------------------------------------

rem 【情報の発信】 固定記事, ブログ, カレンダー, スライドショー, 開館カレンダー, 新着情報
rem 【情報の蓄積】 FAQ, リンクリスト, キャビネット, フォトアルバム, データベース, OPAC, (researchmap連携), (機関リポジトリ)
rem 【情報の収集】 フォーム, 課題管理, (データ収集)
rem 【情報の検索】 サイト内検索, データベース検索
rem 【情報の交換】 掲示板, 施設予約
rem 【情報の整理】 メニュー, タブ
rem 【情報の試行】 テーマチェンジャー
rem 【情報の教育】 (DroneStudy), (CodeStudy)


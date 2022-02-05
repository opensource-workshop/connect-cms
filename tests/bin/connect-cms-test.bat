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
    rem echo.
    rem echo --- キャッシュクリア
    rem php artisan cache:clear
    rem php artisan config:clear

    echo.
    echo --- データベース・クリア
    php artisan db:seed --env=dusk.local --class=TruncateAllTables

    echo.
    echo --- データ・初期追加
    php artisan db:seed --env=dusk.local
)

if "%1" == "fresh" (
    rem 下記は、自動テストDB初期化で行っていないコマンド
    rem echo.
    rem echo --- キャッシュクリア
    rem php artisan cache:clear
    rem php artisan config:clear

    echo.
    echo --- テーブルの再構築
    php artisan migrate:fresh --env=dusk.local

    echo.
    echo --- データ・初期追加
    php artisan db:seed --env=dusk.local
)

rem ---------------------------------------------
rem - 事前準備用の実行
rem ---------------------------------------------

echo.
echo --- データ準備用 - ログ管理 - マニュアルなし
php artisan dusk tests\Browser\Manage\LogManageTest.php no_manual

rem ---------------------------------------------
rem - 管理プラグイン
rem ---------------------------------------------

echo.
echo --- 管理画面アクセス
php artisan dusk tests\Browser\Manage\IndexManageTest.php

echo.
echo --- ページ管理のテスト
php artisan dusk tests\Browser\Manage\PageManageTest.php

echo.
echo --- サイト管理のテスト
php artisan dusk tests\Browser\Manage\SiteManageTest.php

echo.
echo --- ユーザ管理のテスト
php artisan dusk tests\Browser\Manage\UserManageTest.php

echo.
echo --- グループ管理のテスト
php artisan dusk tests\Browser\Manage\GroupManageTest.php

echo.
echo --- セキュリティ管理のテスト
php artisan dusk tests\Browser\Manage\SecurityManageTest.php

echo.
echo --- プラグイン管理のテスト
php artisan dusk tests\Browser\Manage\PluginManageTest.php

echo.
echo --- システム管理のテスト
php artisan dusk tests\Browser\Manage\SystemManageTest.php

echo.
echo --- API管理のテスト
php artisan dusk tests\Browser\Manage\ApiManageTest.php

echo.
echo --- メッセージ管理のテスト
php artisan dusk tests\Browser\Manage\MessageManageTest.php

echo.
echo --- 外部認証管理のテスト
php artisan dusk tests\Browser\Manage\AuthManageTest.php

echo.
echo --- 外部サービス設定のテスト
php artisan dusk tests\Browser\Manage\ServiceManageTest.php

rem ---------------------------------------------
rem - データ管理プラグイン
rem ---------------------------------------------

echo.
echo --- アップロードファイル
php artisan dusk tests\Browser\Manage\UploadfileManageTest.php

echo.
echo --- テーマ管理
php artisan dusk tests\Browser\Manage\ThemeManageTest.php

echo.
echo --- 連番管理
php artisan dusk tests\Browser\Manage\NumberManageTest.php

echo.
echo --- コード管理
php artisan dusk tests\Browser\Manage\CodeManageTest.php

echo.
echo --- ログ管理
php artisan dusk tests\Browser\Manage\LogManageTest.php

echo.
echo --- 祝日管理
php artisan dusk tests\Browser\Manage\HolidayManageTest.php

echo.
echo --- 他システム移行
php artisan dusk tests\Browser\Manage\MigrationManageTest.php

rem ---------------------------------------------
rem - コア
rem ---------------------------------------------

echo.
echo --- ページなし(404)
rem php artisan dusk tests\Browser\Core\PageNotFoundTest.php

echo.
echo --- 権限なし(403)
rem php artisan dusk tests\Browser\Core\PageForbiddenTest.php

echo.
echo --- 初回確認メッセージ動作テスト
rem php artisan dusk tests\Browser\Core\MessageFirstShowTest.php

echo.
echo --- 初回確認メッセージ動作テスト 項目フル入力
rem php artisan dusk tests\Browser\Core\MessageFirstShowFullTest.php

echo.
echo --- 閲覧パスワード付ページテスト
rem php artisan dusk tests\Browser\Core\PagePasswordTest.php

echo.
echo --- ログインテスト
rem php artisan dusk tests\Browser\Core\LoginTest.php

rem ---------------------------------------------
rem - 共通
rem ---------------------------------------------

echo.
echo --- ログイン・ログアウト
php artisan dusk tests\Browser\Common\LoginLogoutTest.php

echo.
echo --- 管理機能
php artisan dusk tests\Browser\Common\AdminLinkTest.php

rem ---------------------------------------------
rem - 一般プラグイン
rem ---------------------------------------------

echo.
echo --- ブログ
rem php artisan dusk tests\Browser\User\BlogTest.php

echo.
echo ※ スクリーンショットの保存先
echo tests\Browser\screenshots

rem ---------------------------------------------
rem - マニュアル
rem ---------------------------------------------




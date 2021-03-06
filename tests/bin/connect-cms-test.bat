@echo off
chcp 932

rem ----------------------------------------------
rem batでまとめてテスト実行
rem > tests\bin\connect-cms-test.bat
rem
rem [How to test]
rem https://github.com/opensource-workshop/connect-cms/wiki/Dusk
rem ----------------------------------------------

rem テストコマンド実行時に１度だけ、自動テストDB初期化をするので不要です。
rem   (see) https://github.com/opensource-workshop/connect-cms/wiki/Dusk#手動でテストdb初期化
rem @php artisan config:clear

if "%1" == "db_clear" (
    rem 下記は、自動テストDB初期化で行っていないコマンド
    echo.
    echo --- キャッシュクリア
    php artisan cache:clear
    rem php artisan config:clear

    echo.
    echo --- データベース・クリア
    php artisan migrate:fresh --env=dusk.local

    rem echo.
    rem echo --- データ・初期追加
    rem php artisan db:seed --env=dusk.local
)

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
rem - 管理プラグイン
rem ---------------------------------------------

echo.
echo --- 管理画面アクセス
rem php artisan dusk tests\Browser\Manage\IndexManageTest.php

echo.
echo --- ページ管理のテスト
rem php artisan dusk tests\Browser\Manage\PageManageTest.php

echo.
echo --- サイト管理のテスト
rem php artisan dusk tests\Browser\Manage\SiteManageTest.php

echo.
echo --- ユーザ管理のテスト
rem php artisan dusk tests\Browser\Manage\UserManageTest.php

echo.
echo --- グループ管理のテスト
rem php artisan dusk tests\Browser\Manage\GroupManageTest.php

echo.
echo --- セキュリティ管理のテスト
rem php artisan dusk tests\Browser\Manage\SecurityManageTest.php

echo.
echo --- プラグイン管理のテスト
rem php artisan dusk tests\Browser\Manage\PluginManageTest.php

echo.
echo --- システム管理のテスト
rem php artisan dusk tests\Browser\Manage\SystemManageTest.php

echo.
echo --- API管理のテスト
rem php artisan dusk tests\Browser\Manage\ApiManageTest.php

echo.
echo --- メッセージ管理のテスト
rem php artisan dusk tests\Browser\Manage\MessageManageTest.php

echo.
echo --- 外部認証管理のテスト
rem php artisan dusk tests\Browser\Manage\AuthManageTest.php

rem ---------------------------------------------
rem - 一般プラグイン
rem ---------------------------------------------

echo.
echo --- ヘッダー
php artisan dusk tests\Browser\User\HeaderAreaTest.php

echo.
echo --- ブログ
rem php artisan dusk tests\Browser\User\BlogTest.php

echo.
echo ※ スクリーンショットの保存先
echo tests\Browser\screenshots

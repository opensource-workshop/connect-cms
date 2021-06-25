@echo off
chcp 932

rem ----------------------------------------------
rem batでまとめてテスト実行
rem > tests\bin\connect-cms-test.bat
rem
rem [How to test]
rem https://github.com/opensource-workshop/connect-cms/wiki/Dusk
rem ----------------------------------------------

@php artisan config:clear

if "%1" == "db_clear" (
    echo.
    echo --- キャッシュクリア
    php artisan cache:clear
    php artisan config:clear

    echo.
    echo --- データベース・クリア
    php artisan migrate:fresh --env=dusk.local

    echo.
    echo --- データ・初期追加
    php artisan db:seed --env=dusk.local
)

rem ---------------------------------------------
rem - コア
rem ---------------------------------------------

echo.
echo --- ページなし
rem php artisan dusk tests\Browser\Core\PageNotFoundTest.php

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
echo ※ スクリーンショットの保存先
echo tests\Browser\screenshots

chcp 932
@echo off

rem ----------------------------------------------
rem [How to test]
rem 開発ライブラリ dusk 等インストール
rem composer install
rem
rem クロームドライバーをインストール
rem php artisan dusk:chrome-driver
rem
rem テストDBの作成して、.env変更（テスト実行すると、データ追加したり、オプション db_clear でDBクリア出来たりするため）
rem #DB_DATABASE=cms
rem DB_DATABASE=cms_test
rem
rem テストコードの実行
rem tests\bin\connect-cms-test.bat
rem tests\bin\connect-cms-test.bat db_clear   ※ 初回はこっち
rem
rem ※ スクリーンショットの保存先
rem tests\Browser\screenshots
rem ----------------------------------------------

@php artisan config:clear

if "%1" == "db_clear" (
    echo.
    echo --- キャッシュクリア
    php artisan cache:clear
    php artisan config:clear

    echo.
    echo --- データベース・クリア
    php artisan migrate:fresh

    echo.
    echo --- データ・初期追加
    php artisan db:seed
)

rem ---------------------------------------------
rem - 管理プラグイン
rem ---------------------------------------------

echo.
echo --- 管理画面アクセス
rem php artisan dusk tests\Browser\Manage\IndexManage.php

echo.
echo --- ページ管理のテスト
rem php artisan dusk tests\Browser\Manage\PageManage.php

echo.
echo --- サイト管理のテスト
rem php artisan dusk tests\Browser\Manage\SiteManage.php

echo.
echo --- ユーザ管理のテスト
rem php artisan dusk tests\Browser\Manage\UserManage.php

echo.
echo --- グループ管理のテスト
rem php artisan dusk tests\Browser\Manage\GroupManage.php

echo.
echo --- セキュリティ管理のテスト
rem php artisan dusk tests\Browser\Manage\SecurityManage.php

echo.
echo --- プラグイン管理のテスト
rem php artisan dusk tests\Browser\Manage\PluginManage.php

echo.
echo --- システム管理のテスト
rem php artisan dusk tests\Browser\Manage\SystemManage.php

echo.
echo --- API管理のテスト
rem php artisan dusk tests\Browser\Manage\ApiManage.php

echo.
echo --- メッセージ管理のテスト
rem php artisan dusk tests\Browser\Manage\MessageManage.php

echo.
echo --- 外部認証管理のテスト
rem php artisan dusk tests\Browser\Manage\AuthManage.php

rem ---------------------------------------------
rem - 一般プラグイン
rem ---------------------------------------------

echo.
echo --- ヘッダー
php artisan dusk tests\Browser\User\HeaderArea.php


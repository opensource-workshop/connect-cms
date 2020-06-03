
// migration の指定方法
// --path でオプションのmigration ファイルのパスを指定します。

// テーブル生成用
php artisan make:migration optionsamples_table --path=database/migrations_option --create=optionsamples

// テーブル修正用
php artisan make:migration add_xxxxxx_id_to_xxxxxx_table --path=database/migrations_option --table=optionsamples

// 実行用
php artisan migrate --path=database/migrations_option


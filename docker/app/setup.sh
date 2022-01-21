cd /var/www/html/connect-cms

# composer インストール
composer install

# .envファイル作成
cp -f .env.example .env

## DB設定
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/g' .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=$DB_DATABASE/g' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=$DB_USERNAME/g' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=$DB_PASSWORD/g' .env

## mailhog設定
sed -i 's/MAIL_HOST=smtp.mailtrap.io/MAIL_HOST=mailhog/g' .env
sed -i 's/MAIL_PORT=2525/MAIL_PORY=1025/g' .env
sed -i 's/MAIL_FROM_ADDRESS=null/MAIL_FROM_ADDRESS=mailhog@mailhog.com/g' .env

# アプリケーションキーの初期化
php artisan key:generate
# DBマイグレーション
php artisan migrate
# seederで初期データインポート
php artisan db:seed

# storageディレクトリとbootstrap/cacheディレクトリをWebサーバから書き込み可能にする
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

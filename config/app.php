<?php

$app_array = [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    //'name' => env('APP_NAME', 'Laravel'),
    'name' => env('APP_NAME', 'Connect-CMS'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    //'timezone' => 'UTC',
    'timezone' => 'Asia/Tokyo',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    //'locale' => 'en',
    'locale' => 'ja',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        // 画像処理
        Intervention\Image\ImageServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        // delete: Laravel6で無くなったクラス
        //'Input' => Illuminate\Support\Facades\Input::class,
        'Carbon' => Carbon\Carbon::class,

        // enums
        'Required' => \App\Enums\Required::class,
        'ReservationColumnType' => \App\Enums\ReservationColumnType::class,
        'ReservationCalendarDisplayType' => \App\Enums\ReservationCalendarDisplayType::class,
        'FormColumnType' => \App\Enums\FormColumnType::class,
        'DatabaseColumnType' => \App\Enums\DatabaseColumnType::class,
        'DatabaseColumnRoleName' => \App\Enums\DatabaseColumnRoleName::class,
        'DatabaseSortFlag' => \App\Enums\DatabaseSortFlag::class,
        'DatabaseRoleName' => \App\Enums\DatabaseRoleName::class,
        'DayOfWeek' => \App\Enums\DayOfWeek::class,
        'Bs4TextColor' => \App\Enums\Bs4TextColor::class,
        'Bs4Color' => \App\Enums\Bs4Color::class,
        'RadiusType' => \App\Enums\RadiusType::class,
        'MinutesIncrements' => \App\Enums\MinutesIncrements::class,
        'ConnectLocale' => \App\Enums\ConnectLocale::class,
        'GroupType' => \App\Enums\GroupType::class,
        'WhatsnewsTargetPlugin' => \App\Enums\WhatsnewsTargetPlugin::class,
        'CsvCharacterCode' => \App\Enums\CsvCharacterCode::class,
        'ShowType' => \App\Enums\ShowType::class,
        'UseType' => \App\Enums\UseType::class,
        'PermissionType' => \App\Enums\PermissionType::class,
        'StatusType' => \App\Enums\StatusType::class,
        'DisplayNumberType' => \App\Enums\DisplayNumberType::class,
        'FormStatusType' => \App\Enums\FormStatusType::class,
        'AuthMethodType' => \App\Enums\AuthMethodType::class,
        'CodeColumn' => \App\Enums\CodeColumn::class,
        'UserColumnType' => \App\Enums\UserColumnType::class,
        'NoticeJobType' => \App\Enums\NoticeJobType::class,
        'CountryCodeAlpha3' => \App\Enums\CountryCodeAlpha3::class,
        'UserStatus' => \App\Enums\UserStatus::class,
        'PluginName' => \App\Enums\PluginName::class,
        'LearningtaskUseFunction' => \App\Enums\LearningtaskUseFunction::class,
        'ContentOpenType' => \App\Enums\ContentOpenType::class,
        'RoleName' => \App\Enums\RoleName::class,
        'LearningtaskUserJoinFlag' => \App\Enums\LearningtaskUserJoinFlag::class,
        'LearningtasksExaminationColumn' => \App\Enums\LearningtasksExaminationColumn::class,
        'CounterDesignType' => \App\Enums\CounterDesignType::class,
        'BaseLoginRedirectPage' => \App\Enums\BaseLoginRedirectPage::class,
        'BlogFrameConfig' => \App\Enums\BlogFrameConfig::class,
        'BlogDisplayCreatedName' => \App\Enums\BlogDisplayCreatedName::class,
        'BaseHeaderFontColorClass' => \App\Enums\BaseHeaderFontColorClass::class,
        'UploadMaxSize' => \App\Enums\UploadMaxSize::class,
        'SearchsTargetPlugin' => \App\Enums\SearchsTargetPlugin::class,
        'LayoutArea' => \App\Enums\LayoutArea::class,
        'CabinetFrameConfig' => \App\Enums\CabinetFrameConfig::class,
        'CabinetSort' => \App\Enums\CabinetSort::class,
        'LinklistType' => \App\Enums\LinklistType::class,
        'AuthLdapDnType' => \App\Enums\AuthLdapDnType::class,
        'MemoryLimitForImageResize' => \App\Enums\MemoryLimitForImageResize::class,
        'NumberOfPdfThumbnail' => \App\Enums\NumberOfPdfThumbnail::class,

        // utils
        'DateUtils' => \App\Utilities\Date\DateUtils::class,
        'StringUtils' => \App\Utilities\String\StringUtils::class,

        // Models
        'Plugins' => \App\Models\Core\Plugins::class,
        'FrameConfig' => \App\Models\Core\FrameConfig::class,
        'Configs' => \App\Models\Core\Configs::class,
        'Like' => \App\Models\Common\Like::class,

        // 画像処理
        'Image' => Intervention\Image\Facades\Image::class,

    ],

];

/**
 * 外部プラグイン用に定義したenumファイル（enums_optionディレクトリ）をaliasesに登録
 */
// configディレクトリを起点に「App\EnumsOption」ディレクトリを取得
$path_enums_option = str_replace(DIRECTORY_SEPARATOR . 'config', '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'EnumsOption' . DIRECTORY_SEPARATOR;
// 「App\EnumsOption」ディレクトリ配下のファイルをフルパスで取得
$fullpaths = glob($path_enums_option . '*');
foreach ($fullpaths as $fullpath) {
    // 拡張子を除外
    $fullpath_omit_extention = str_replace('.php', '', $fullpath);
    // ディレクトリ部分を除外
    $enums_option_name = str_replace($path_enums_option, '', $fullpath_omit_extention);
    // ネームスペースを追加
    $enums_option_name_with_namespace = '\\App\\EnumsOption\\' . $enums_option_name;
    // Laravelのエイリアスに登録
    $app_array['aliases'][$enums_option_name] = get_class(new $enums_option_name_with_namespace);
}

return $app_array;

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Core\ClassController;
use App\Http\Controllers\Core\DefaultController;

/*
    [URL ルール]
    コアURL          /core/{action_type(frame等)}/{action}/{page_id?}/{frame_id?}
    管理画面URL      /manage/{plugin_name}/{action?}/{id?}
    一般画面URL      /plugin/{plugin_name}/{action}/{page_id?}/{id?}

    [action_type | plugin_name] クラス名。インスタンスの生成で使用する。
    [action] メソッド名。そのメソッドを呼び出す。
*/

// 認証系アクション
// Auth::routes();
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
 
Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset');

//ユーザー登録
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');

// システム管理者 or ユーザ管理者の場合、OK
//Route::group(['middleware' => ['auth', 'can:system_user-admin']], function () {
    //Route::post('register', 'Auth\RegisterController@register');
    Route::post('register', 'Auth\RegisterController@register');
//});


// テスト用アクション
//Route::get('/test/{id?}', 'Core\TestController@invokeGet');
//Route::post('/test/{id?}', 'Core\TestController@invokePost');

// コアのget処理(Frame関係)
Route::get('/core/{action_type}/{action}/{page_id?}/{frame_id?}', 'Core\ClassController@invokeGetCore');

// コアのpost処理(Frame関係)
Route::post('/core/{action_type}/{action}/{page_id?}/{frame_id?}/{arg?}', 'Core\ClassController@invokePostCore');

// コアのAPI処理
Route::get('/api/{plugin_name}/{action}/{arg1?}/{arg2?}/{arg3?}/{arg4?}/{arg5?}', 'Core\ApiController@invokeApi');

// 管理画面getアクション：管理画面用のクラスをURL をもとに、ClassController で呼び出す。
Route::get('/manage/{plugin_name}/{action?}/{id?}', 'Core\ClassController@invokeGetManage');

// 管理画面postアクション
Route::post('/manage/{plugin_name}/{action?}/{id?}', 'Core\ClassController@invokePostManage');

// 一般プラグインの表示系アクション
Route::get('/plugin/{plugin_name}/{action}/{page_id?}/{frame_id?}/{id?}', 'Core\DefaultController@invokePost');

// 一般プラグインの更新系アクション（画面がある場合）
Route::post('/plugin/{plugin_name}/{action}/{page_id?}/{frame_id?}/{id?}', 'Core\DefaultController@invokePost');

// 一般プラグインの更新系アクション（リダイレクトする場合）
Route::post('/redirect/plugin/{plugin_name}/{action}/{page_id?}/{frame_id?}/{id?}', 'Core\DefaultController@invokePostRedirect');

// 一般プラグインのダウンロード系アクション
Route::post('/download/plugin/{plugin_name}/{action}/{page_id?}/{frame_id?}/{id?}', 'Core\DefaultController@invokePostDownload');

// CSS の取得アクション
Route::get('/file/css/{page_id?}.css', 'Core\UploadController@getCss');

// アップロードファイルの保存アクション
Route::post('/upload', 'Core\UploadController@postFile');

// アップロードファイルの取得アクション
Route::get('/file/{id?}', 'Core\UploadController@getFile');

// 基本のアクション
// コアの画面処理や各プラグインの処理はここから呼び出す。
Route::get( '{all}', 'Core\DefaultController')->where('all', '.*');
Route::post('{all}', 'Core\DefaultController')->where('all', '.*');

// ログの書式
//Log::debug($request->action);


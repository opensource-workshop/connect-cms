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
Auth::routes();

// コアのget処理(Frame関係)
//Route::get('/core/{action_type}/{action}/{page_id?}/{frame_id?}', 'Core\ClassController@invokeGetCore');

// コアのpost処理(Frame関係)
Route::post('/core/{action_type}/{action}/{page_id?}/{frame_id?}', 'Core\ClassController@invokePostCore');

// 管理画面getアクション：管理画面用のクラスをURL をもとに、ClassController で呼び出す。
Route::get('/manage/{plugin_name}/{action?}/{page_id?}', 'Core\ClassController@invokeGetManage');

// 管理画面postアクション
Route::post('/manage/{plugin_name}/{action?}/{id?}', 'Core\ClassController@invokePostManage');

// 一般プラグインの表示系アクション
Route::get('/plugin/{plugin_name}/{action}/{page_id?}/{frame_id?}/{id?}', 'Core\DefaultController@invokePost');

// 一般プラグインの更新系アクション（画面がある場合）
Route::post('/plugin/{plugin_name}/{action}/{page_id?}/{frame_id?}/{id?}', 'Core\DefaultController@invokePost');

// 一般プラグインの更新系アクション（リダイレクトする場合）
Route::post('/redirect/plugin/{plugin_name}/{action}/{page_id?}/{frame_id?}/{id?}', 'Core\DefaultController@invokePostRedirect');

// CSS の取得アクション
Route::get('/file/css/{page_id?}.css', 'Core\UploadController@getCss');

// アップロードファイルの取得アクション
Route::get('/file/{id?}', 'Core\UploadController@getFile');

// 基本のアクション
// コアの画面処理や各プラグインの処理はここから呼び出す。
Route::get( '{all}', 'Core\DefaultController')->where('all', '.*');
Route::post('{all}', 'Core\DefaultController')->where('all', '.*');

// ログの書式
//Log::debug($request->action);


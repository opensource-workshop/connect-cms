<?php

namespace App\PluginsOption\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Frame;
use App\Models\Core\Configs;

use App\Plugins\User\UserPluginBase;

/**
 * オプション・ユーザープラグイン
 *
 * オプション・ユーザ用プラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Contoroller
 */
class UserPluginOptionBase extends UserPluginBase
{
    // 2020-06-03 現在、処理は入れていないが、今後、オプションプラグイン特有のチェックなどが入ると想定しています。
}

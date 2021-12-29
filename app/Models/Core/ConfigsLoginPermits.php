<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class ConfigsLoginPermits extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['apply_sequence', 'ip_address', 'role', 'reject', 'memo'];

    /**
     * 権限名の取得
     */
    public static function getRoleName($config_login_permit)
    {
        if ($config_login_permit->role == 'role_article_admin') {
            return 'コンテンツ管理者';
        } elseif ($config_login_permit->role == 'role_arrangement') {
            return 'プラグイン管理者';
        } elseif ($config_login_permit->role == 'role_article') {
            return 'モデレータ';
        } elseif ($config_login_permit->role == 'role_approval') {
            return '承認者';
        } elseif ($config_login_permit->role == 'role_reporter') {
            return '編集者';
        } elseif ($config_login_permit->role == 'role_guest') {
            return 'ゲスト';
        } elseif ($config_login_permit->role == 'admin_system') {
            return 'システム管理者';
        } elseif ($config_login_permit->role == 'admin_site') {
            return 'サイト管理者';
        } elseif ($config_login_permit->role == 'admin_page') {
            return 'ページ管理者';
        } elseif ($config_login_permit->role == 'admin_user') {
            return 'ユーザ管理者';
        }
    }
}

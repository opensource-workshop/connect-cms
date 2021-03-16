<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Notifications\PasswordResetNotification;
// use App\Notifications\EmailVerifyNotification;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    use Notifiable;

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['created_at', 'updated_at'];

    // ユーザーの権限セット
    public $user_roles = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'userid', 'password', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * パスワードリセット通知の送信をオーバーライド
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    // /**
    //  * Send the email verification notification.
    //  * (OverWrite) 親クラス Illuminate\Foundation\Auth\User 定義のtrait Illuminate\Auth\MustVerifyEmail::sendEmailVerificationNotification();
    //  *
    //  * @return void
    //  */
    // public function sendEmailVerificationNotification()
    // {
    //     $this->notify(new EmailVerifyNotification);
    // }

    /**
     * 状態から一覧表示の背景クラスを返却
     */
    public function getStstusBackgroundClass()
    {
        if ($this->status == \UserStatus::not_active) {
            // 利用停止中
            return "bg-warning";
        } elseif ($this->status == \UserStatus::approval_pending) {
            // 承認待ち
            return "bg-secondary text-white";
        }
        return "";
    }

    /**
     * 権限の文字アイコンタグ取得
     */
    public function getRoleStringTag()
    {
        // 権限データがあるか確認
        if (empty($this->view_user_roles)) {
            return "";
        }

        // コンテンツ権限
        $content_roles = "";

        foreach ($this->view_user_roles as $view_user_role) {
            if ($view_user_role->role_name == 'role_article_admin') {
                $content_roles .= '<span class="badge badge-danger">コ</span> ';
            }
            if ($view_user_role->role_name == 'role_arrangement') {
                $content_roles .= '<span class="badge badge-primary">プ</span> ';
            }
            if ($view_user_role->role_name == 'role_article') {
                $content_roles .= '<span class="badge badge-success">モ</span> ';
            }
            if ($view_user_role->role_name == 'role_approval') {
                $content_roles .= '<span class="badge badge-warning">承</span> ';
            }
            if ($view_user_role->role_name == 'role_reporter') {
                $content_roles .= '<span class="badge badge-info">編</span> ';
            }
        }

        // 管理権限
        $admin_roles = "";
        foreach ($this->view_user_roles as $view_user_role) {
            if ($view_user_role->role_name == 'admin_system') {
                $admin_roles .= '<span class="badge badge-danger">シ</span> ';
            }
            if ($view_user_role->role_name == 'admin_site') {
                $admin_roles .= '<span class="badge badge-primary">サ</span> ';
            }
            if ($view_user_role->role_name == 'admin_page') {
                $admin_roles .= '<span class="badge badge-success">ペ</span> ';
            }
            if ($view_user_role->role_name == 'admin_user') {
                $admin_roles .= '<span class="badge badge-warning">ユ</span> ';
            }
        }

        if (!empty($content_roles) && !empty($admin_roles)) {
            return $content_roles . "<br />" . $admin_roles;
        }

        return $content_roles . $admin_roles;
    }

    /**
     * hasMany 設定
     */
/*
    public function group_user()
    {
        return $this->hasMany('App\User');
    }
*/
}

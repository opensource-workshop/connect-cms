<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Notifications\PasswordResetNotification;

use App\Enums\UserStatus;

use App\Models\Common\GroupUser;

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
        'name', 'email', 'userid', 'password', 'status', 'add_token', 'add_token_created_at',
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

    /**
     * 状態から一覧表示の背景クラスを返却
     */
    public function getStstusBackgroundClass()
    {
        if ($this->status == UserStatus::not_active) {
            // 利用停止中
            return "bg-warning";
            // return "bg-secondary text-white";
        } elseif ($this->status == UserStatus::temporary) {
            // 仮登録
            return "bg-warning";
        } elseif ($this->status == UserStatus::temporary_delete) {
            // 仮削除
            return "cc-bg-red";
        } elseif ($this->status == UserStatus::pending_approval) {
            // 承認待ち
            return "bg-info";
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
     * 仮登録のinput disable 属性の要否を判断して返す。
     */
    public function getStstusTemporaryDisabled($enum_value)
    {
        // 選択肢が仮登録の場合のみ、disabled の判定をする。
        if ($enum_value != UserStatus::temporary) {
            return "";
        }

        // 仮登録は、ユーザが自分で登録する際のメールアドレス確認用という位置づけ。
        // そのため、新規登録時や利用可能、利用不可状態からの仮登録への変更はできないようにする。
        // 判定としては、現在、仮登録の場合のみ、仮登録は選択可能だが、違う場合は、仮登録へ変更させない。
        if ($this->status == UserStatus::temporary) {
            return "";
        }
        return "disabled";
    }

    /**
     * 仮登録/仮削除のinput disable 属性の要否を判断して返す。
     */
    public function getStstusDisabled($enum_value, $is_function_edit)
    {
        // 仮登録の非表示判定
        $disabled = $this->getStstusTemporaryDisabled($enum_value);
        if ($disabled) {
            // 非表示ならここで返す
            return $disabled;
        }

        // 登録の時は 仮削除 を選択させない
        if (!$is_function_edit && $enum_value == UserStatus::temporary_delete) {
            return "disabled";
        }

        // 承認待ち以外のステータスから承認待ちへできないようにする
        if ($enum_value ===  UserStatus::pending_approval
            && $this->status !== UserStatus::pending_approval) {
            return "disabled";
        }

        return "";
    }

    /**
     * ループ項目を区切り文字を使って文字列に変換する
     */
    public function convertLoopValue($LoopColums, $childColum, $separator = ', ')
    {
        $value = '';

        // ループ項目が無ければ終了
        if (isset($this->$LoopColums)) {
            foreach ($this->$LoopColums as $LoopColum) {
                // 区切り文字で連結
                $value .= $LoopColum->$childColum . $separator;
            }
            // 末尾区切り文字を削除
            $value = rtrim($value, $separator);
        }
        return $value;
    }

    /**
     * hasMany 設定
     * - hasManyは、$user->group_users で使うので、変数名と同義になるので、このメソッド名はphpcs除外
     * - hasManyは、値があるなしに関わらず collection 型を返す。値がなければ空の collection 型を返す。
     */
    public function group_users()    // phpcs:ignore
    {
        return $this->hasMany(GroupUser::class);
    }
}

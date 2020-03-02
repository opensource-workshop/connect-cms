<?php

namespace App;

use App\Notifications\PasswordResetNotification;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;

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
        //'name', 'email', 'password',
        'name', 'email', 'userid', 'password', 'role',
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
     * ユーザーのロールを取得
     * bugfix: ログイン時、「ログイン状態を維持する」ONで1日たってからブラウザアクセスすると例外が発生するバグに対応
     *         アクセサを定義して、user_rolesがnullの場合、arrayを返す
     *         アクセサ定義の参考 https://readouble.com/laravel/5.5/ja/eloquent-mutators.html#accessors-and-mutators
     */
    public function getUserRolesAttribute($value)
    {
        return is_null($value) ? [] : $value;
    }
}

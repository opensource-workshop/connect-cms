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
     * hasMany 設定
     */
/*
    public function group_user()
    {
        return $this->hasMany('App\User');
    }
*/
}

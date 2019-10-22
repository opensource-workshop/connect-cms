<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

// add by nagahara@opensource-workshop.jp
use Illuminate\Support\Facades\Log;

use App\User;
use App\Models\Core\UsersRoles;

class ConnectEloquentUserProvider extends EloquentUserProvider
{

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        // mod by nagahara@opensource-workshop.jp
        //        return $model->newQuery()
        //            ->where($model->getAuthIdentifierName(), $identifier)
        //            ->first();

        $ret = $model->newQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();

        // user roles を追加
        $users_roles = new UsersRoles();
        $ret->user_roles = $users_roles->getUsersRoles($identifier);

        return $ret;

    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $user->setRememberToken($token);

        $timestamps = $user->timestamps;

        $user->timestamps = false;

        // ログアウト時にuser_roles をセットしに行ってエラーになるため、update 処理に変更 by nagahara@opensource-workshop.jp
        // $user->save();
        User::where('id', $user->id)->update(['remember_token' => $token]);

        $user->timestamps = $timestamps;
    }
}

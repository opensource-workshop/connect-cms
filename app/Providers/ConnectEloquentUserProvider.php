<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
//use Illuminate\Http\Request;

// add by nagahara@opensource-workshop.jp
use Illuminate\Support\Facades\Log;

use App\Traits\ConnectCommonTrait;

use App\User;
use App\Models\Core\UsersRoles;

/**
 * Laravel標準のEloquentUserProviderを継承して、Connect-CMS用にカスタマイズしたログイン処理です。
 */
class ConnectEloquentUserProvider extends EloquentUserProvider
{

    use ConnectCommonTrait;

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {

        // ログイン前だけれど、$user にはユーザーオブジェクトが入っている。
        // IP アドレスチェックなどでログインの可否をチェック
        $judgment_login = $this->judgmentLogin($user);

        if (!$judgment_login) {
            abort(403, 'ログイン制限によるログイン拒否');
        }

        // 通常のログイン
        return parent::validateCredentials($user, $credentials);
    }

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

        // guest 権限は自動的に付与する。
        $ret->user_roles['base']['role_guest'] = 1;

        return $ret;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * bugfix: ログイン維持（Remember Me）の時、user roles を追加が実行されないバグ修正
     *         retrieveById()は、セッションのユーザIDがあれば処理する関係で、ログイン維持の場合、一旦セッションが消えるため、実行されない
     *         そのため、retrieveByToken()をオーバーライトして Userクラスへの追加処理（user roles を追加）を行う
     *
     * copy by Illuminate\Auth\EloquentUserProvider::retrieveByToken()
     * @see \Illuminate\Auth\SessionGuard Authクラスの実クラス
     * @see \Illuminate\Auth\EloquentUserProvider 親クラス
     *
     * 「呼び出し順」
     * Illuminate\Support\Facades\Auth::user()
     * ↓
     * \Illuminate\Auth\SessionGuard::user()
     *     $this->userFromRecaller($recaller);
     * ↓
     * \Illuminate\Auth\SessionGuard::userFromRecaller()
     *     $this->provider->retrieveByToken()
     * ↓
     * 当メソッド呼ばれる
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        // 親メソッド呼び出し
        $rememberToken = parent::retrieveByToken($identifier, $token);

        // Log::notice('retrieveByToken');
        if ($rememberToken) {
            // 戻り値ありなら、ログイン維持（Remember Me）での認証時
            // Log::notice('Remember Me Logged in 2');

            // user roles を追加
            $users_roles = new UsersRoles();
            $rememberToken->user_roles = $users_roles->getUsersRoles($identifier);

            // guest 権限は自動的に付与する。
            $rememberToken->user_roles['base']['role_guest'] = 1;
        }

        // Log::notice('retrieveByToken-end');
        return $rememberToken;
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

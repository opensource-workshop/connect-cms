<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
//use Illuminate\Http\Request;

// add by nagahara@opensource-workshop.jp
use Illuminate\Support\Facades\Log;

use App\Traits\ConnectCommonTrait;
use App\Traits\ConnectRoleTrait;

use App\User;
use App\Models\Core\UsersRoles;
use App\Models\Core\ConfigsLoginPermits;
use App\Models\Core\Configs;

/**
 * Laravel標準のEloquentUserProviderを継承して、Connect-CMS用にカスタマイズしたログイン処理です。
 */
class ConnectEloquentUserProvider extends EloquentUserProvider
{
    use ConnectCommonTrait, ConnectRoleTrait;

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
     * ログイン可否チェック
     */
    private function judgmentLogin($user)
    {
        // IP アドレス取得
        $remote_ip = \Request::ip();
        //Log::debug("--- IP：" . $remote_ip);

        // ログイン可否の基本設定を取得
        $configs = Configs::where('name', 'login_reject')->first();

        // ログイン可否の基本
        $login_reject = 0;
        if (!empty($configs)) {
            $login_reject = $configs->value;
        }
        //Log::debug("基本：" . $login_reject);

        // ユーザーオブジェクトにロールデータを付与
        $users_roles = new UsersRoles();
        $user->user_roles = $users_roles->getUsersRoles($user->id);
        // Log::debug($user);
        // Log::debug($user->user_roles);

        // ログイン可否の個別設定を取得
        $configs_login_permits = ConfigsLoginPermits::orderBy('apply_sequence', 'asc')->get();

        // ログイン可否の個別設定がない場合はここで判断
        if (empty($configs_login_permits)) {
            return ($login_reject == 0) ? true : false;
        }

        // ログイン可否の個別設定をループ
        foreach ($configs_login_permits as $configs_login_permit) {
            // IPアドレスが範囲内か
            if (!$this->isRangeIp($remote_ip, $configs_login_permit->ip_address)) {
                // IPアドレスが範囲外なら、チェック的にはOKなので、次のチェックへ。
                //Log::debug("IP範囲外：" . $remote_ip . "/" . $configs_login_permit->ip_address);
                continue;
            }

            // 権限が範囲内か
            if (empty($configs_login_permit->role)) {
                // ロールが入っていない（全対象）の場合は、対象レコードとなるので、設定されている可否を使用
                //Log::debug("role空で対象：" . $configs_login_permit->reject);
                $login_reject = $configs_login_permit->reject;
            } elseif ($this->checkRole($user, $configs_login_permit->role)) {
                // 許可/拒否設定が自分のロールに合致すれば、対象の許可/拒否設定を反映
                //Log::debug("role合致で対象：" . $configs_login_permit->reject);
                $login_reject = $configs_login_permit->reject;
            }
        }
        // 設定可否の適用
        //Log::debug("最終：" . $login_reject);
        return ($login_reject == 0) ? true : false;
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

        if ($ret) {
            // user roles を追加
            $users_roles = new UsersRoles();
            $ret->user_roles = $users_roles->getUsersRoles($identifier);

            // guest 権限は自動的に付与する。
            $ret->user_roles['base']['role_guest'] = 1;    
        }

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

<?php

namespace App\Utilities\Token;

use Illuminate\Support\Str;

class TokenUtils
{
    /**
     * Create a new token.(メール送信用でユーザのみ知る. DB保存しない)
     *
     * @return string
     *
     * @see \Illuminate\Auth\Passwords\DatabaseTokenRepository ::createNewToken() to copy
     * @see \Illuminate\Auth\Passwords\PasswordBrokerManager ::createTokenRepository() to partial copy
     */
    public static function createNewToken()
    {
        $key = config('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $hashKey = $key;

        return hash_hmac('sha256', Str::random(40), $hashKey);
    }

    /**
     * トークンをハッシュ化（DB保存用）
     *
     * @return string
     *
     * @see \Illuminate\Auth\Passwords\DatabaseTokenRepository ::getPayload() to partial copy
     */
    public static function makeHashToken($token)
    {
        $app = app();
        $hasher = $app['hash'];
        return $hasher->make($token);
    }

    /**
     * Validate the given token.
     *
     * @param  string  $check_token
     * @param  string  $record_token
     * @param  string  $record_created_at
     * @param  int  $expire 初期値60分
     * @return bool
     *
     * @see \Illuminate\Auth\Passwords\DatabaseTokenRepository ::exists() to partial copy
     */
    public static function tokenExists($check_token, $record_token, $record_created_at, $expire = 60)
    {
        // var_dump($check_token, $record_token, $record_created_at, $expire);

        $app = app();
        // \Illuminate\Contracts\Hashing\Hasher
        // \Illuminate\Support\Facades\Hash;
        // \Illuminate\Hashing\BcryptHasher   ← \Illuminate\Contracts\Hashing\Hasher 継承
        $hasher = $app['hash'];

        // var_dump(! self::tokenExpired($record_created_at, $expire), $hasher->check($check_token, $record_token));

        // トークン期限過ぎてないか
        // トークン一致するか
        // \Illuminate\Hashing\BcryptHasher::check()
        return ! self::tokenExpired($record_created_at, $expire) &&
                    $hasher->check($check_token, $record_token);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string  $createdAt
     * @param  int  $expire 初期値60分
     * @return bool
     *
     * @see \Illuminate\Auth\Passwords\DatabaseTokenRepository ::tokenExpired() to copy
     */
    private static function tokenExpired($createdAt, $expire = 60)
    {
        if (empty($createdAt)) {
            // true = 期限切れ(過去)
            return true;
        }

        $expires = $expire * 60;
        // isPast() = 過去かどうか
        return \Carbon::parse($createdAt)->addSeconds($expires)->isPast();
    }
}

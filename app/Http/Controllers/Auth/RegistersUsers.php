<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\Validator;

// Connect-CMS 用設定データ
use App\User;
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Models\Core\UsersInputCols;
use App\Traits\ConnectCommonTrait;
use App\Traits\ConnectMailTrait;

use App\Plugins\Manage\UserManage\UsersTool;
use App\Utilities\Token\TokenUtils;
use App\Rules\CustomVali_TokenExists;
use App\Providers\RouteServiceProvider;

trait RegistersUsers
{
    use RedirectsUsers;
    use ConnectCommonTrait;
    use ConnectMailTrait;

    /**
     * Show the application registration form.
     * ユーザー登録画面表示（自動登録）
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'general')->orWhere('category', 'user_register')->get();
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config['name']] = $config['value'];
        }
        $configs = $configs_array;

        // ログインしているユーザー情報を取得
        //$user = Auth::user();

        // ユーザ登録の権限チェック
        //if (isset($user) && ($user->role == 1 || $user->role == 3)) {
        if ($this->isCan('admin_user')) {
            // ユーザ登録の権限があればOK
        } elseif ($configs_array['user_register_enable'] != "1") {
            // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
            abort(403);
        }

        //// ユーザの追加項目.
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects();
        // カラムの登録データ
        $input_cols = null;

        // フォームの初期値として空のユーザオブジェクトを渡す。
        return view('auth.register', [
            "user" => new User(),
            "configs" => $configs,
            'users_columns' => $users_columns,
            'users_columns_id_select' => $users_columns_id_select,
            'input_cols' => $input_cols,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // 設定の取得
        // $configs = Configs::where('category', 'user_register')->get();
        $configs = Configs::get();

        // ユーザ登録の権限チェック
        //if (isset($user) && ($user->role == 1 || $user->role == 3)) {
        if ($this->isCan('admin_user')) {
            // ユーザ登録の権限があればOK
        } elseif (Configs::getConfigsValue($configs, 'user_register_enable') != "1") {
            // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
            //Log::debug("register 403.");
            abort(403);
        }

        // ユーザーデータ登録
        event(new Registered($user = $this->create($request->all())));

        // ユーザー管理権限がある場合は、各権限の付与
        if ($this->isCan('admin_user')) {
            // ユーザ権限の登録
            if (!empty($request->base)) {
                foreach ($request->base as $role_name => $value) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
                        'target'     => 'base',
                        'role_name'  => $role_name,
                        'role_value' => 1
                    ]);
                }
            }

            // 管理権限の登録
            if (!empty($request->manage)) {
                foreach ($request->manage as $role_name => $value) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
                        'target'     => 'manage',
                        'role_name'  => $role_name,
                        'role_value' => 1
                    ]);
                }
            }

            // 役割設定の登録
            if (!empty($request->original_role)) {
                foreach ($request->original_role as $original_role => $value) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
                        'target'     => 'original_role',
                        'role_name'  => $original_role,
                        'role_value' => 1
                    ]);
                }
            }
        }

        // ユーザー自動登録（未ログイン）の場合、.env のSELF_REGISTER_ROLE を元に権限登録する。
        if (!Auth::user()) {
            $self_register_base_roles_env = config('connect.SELF_REGISTER_BASE_ROLES');
            $self_register_base_roles = array();
            if (!empty($self_register_base_roles_env)) {
                $self_register_base_roles = explode(',', $self_register_base_roles_env);
            }
            if (!empty($self_register_base_roles)) {
                foreach ($self_register_base_roles as $self_register_base_role) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
                        'target'     => 'base',
                        'role_name'  => $self_register_base_role,
                        'role_value' => 1
                    ]);
                }
            }
        }

        // ユーザー自動登録（未ログイン）
        if (!Auth::user()) {
            // session()->flash('flash_message_for_header', 'ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。');


            // 登録者に仮登録メールを送信する
            if (Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_flag')) {
                // *** 仮登録
                // ユーザ側のみメール送信する

                // *** トークン
                // トークン生成 (メール送信用でユーザのみ知る. DB保存しない)
                $user_token = TokenUtils::createNewToken();
                // トークンをハッシュ化（DB保存用）
                $record_token = TokenUtils::makeHashToken($user_token);

                $user->add_token = $record_token;
                $user->add_token_created_at = new \Carbon();
                $user->save();

                // *** メール送信
                // メール件名の組み立て
                $subject = Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_subject');

                // メール件名内のサイト名文字列を置換
                $subject = str_replace('[[site_name]]', Configs::getConfigsValue($configs, 'base_site_name'), $subject);
                // メール件名内の登録日時を置換
                $todatetime = date("Y/m/d H:i:s");
                $subject = str_replace('[[to_datetime]]', $todatetime, $subject);

                // メール本文の組み立て
                $mail_format = Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_format');
                $contents_text = $this->getMailContentsText($configs, $user);
                $mail_text = str_replace('[[body]]', $contents_text, $mail_format);

                // 本登録URL
                $entry_url = url('/') . "/register/confirmToken/{$user->id}/{$user_token}";
                $mail_text = str_replace('[[entry_url]]', $entry_url, $mail_text);
                // メール本文内のサイト名文字列を置換
                $mail_text = str_replace('[[site_name]]', Configs::getConfigsValue($configs, 'base_site_name'), $mail_text);
                // メール本文内の登録日時を置換
                $mail_text = str_replace('[[to_datetime]]', $todatetime, $mail_text);

                // メールオプション
                $mail_options = ['subject' => $subject, 'template' => 'mail.send'];

                // メール送信（ユーザー側）
                $user_mailaddress = $user->email;
                if (!empty($user_mailaddress)) {
                    // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                    $this->sendMail($user_mailaddress, $mail_options, ['content' => $mail_text], 'RegisterController');
                }

                // ユーザー自動登録（未ログイン）の場合の登録完了メッセージ。
                session()->flash('flash_message_for_header', Configs::getConfigsValue($configs, 'user_register_temporary_regist_after_message'));
            } else {
                // *** 本登録

                // 本登録時のメール送信
                $this->sendMailToActive($configs, $user);

                // ユーザー自動登録（未ログイン）の場合の登録完了メッセージ。
                session()->flash('flash_message_for_header', Configs::getConfigsValue($configs, 'user_register_after_message'));
            }
        }

        // 作成したユーザでのログイン処理は行わない。mod by nagahara@opensource-workshop.jp
        // $this->guard()->login($user);

        //Log::debug("register end brfore.");
        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
    }

    /**
     * トークンを使った本登録の確定画面表示
     */
    private function getMailContentsText($configs, $user)
    {
        // メールの内容
        $contents_text = '';
        $contents_text .= "ユーザ名： " . $user->name . "\n";
        $contents_text .= "ログインID： " . $user->userid . "\n";
        $contents_text .= "eメールアドレス： " . $user->email . "\n";

        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();

        // ユーザーカラムの登録データ
        $users_input_cols = UsersInputCols::where('users_id', $user->id)
                                            ->get()
                                            // keyをusers_input_colsにした結果をセット
                                            ->mapWithKeys(function ($item) {
                                                return [$item['users_columns_id'] => $item];
                                            });

        foreach ($users_columns as $users_column) {
            $value = "";
            if (is_array($users_input_cols[$users_column->id])) {
                $value = implode(UsersTool::CHECKBOX_SEPARATOR, $users_input_cols[$users_column->id]->value);
            } else {
                $value = $users_input_cols[$users_column->id]->value;
            }

            // メールの内容
            $contents_text .= $users_column->column_name . "：" . $value . "\n";
        }

        if (Configs::getConfigsValue($configs, 'user_register_requre_privacy')) {
            // 同意設定ONの場合、同意は必須のため、必ず文字列をセットする。
            $contents_text .= "個人情報保護方針への同意 ： 以下の内容に同意します。\n";
        }

        // 最後の改行を除去
        $contents_text = trim($contents_text);
        return $contents_text;
    }

    /**
     * 本登録時のメール送信
     */
    private function sendMailToActive($configs, $user)
    {
        // 以下のアドレスにメール送信する
        $user_register_mail_send_flag = Configs::getConfigsValue($configs, 'user_register_mail_send_flag');
        // 登録者にメール送信する
        $user_register_user_mail_send_flag = Configs::getConfigsValue($configs, 'user_register_user_mail_send_flag');

        // メール送信
        if ($user_register_mail_send_flag || $user_register_user_mail_send_flag) {
            // メール件名の組み立て
            $subject = Configs::getConfigsValue($configs, 'user_register_mail_subject');

            // メール件名内のサイト名文字列を置換
            $subject = str_replace('[[site_name]]', Configs::getConfigsValue($configs, 'base_site_name'), $subject);
            // メール件名内の登録日時を置換
            $todatetime = date("Y/m/d H:i:s");
            $subject = str_replace('[[to_datetime]]', $todatetime, $subject);

            // メール本文の組み立て
            $mail_format = Configs::getConfigsValue($configs, 'user_register_mail_format');
            $contents_text = $this->getMailContentsText($configs, $user);
            $mail_text = str_replace('[[body]]', $contents_text, $mail_format);

            // メール本文内のサイト名文字列を置換
            $mail_text = str_replace('[[site_name]]', Configs::getConfigsValue($configs, 'base_site_name'), $mail_text);
            // メール本文内の登録日時を置換
            $mail_text = str_replace('[[to_datetime]]', $todatetime, $mail_text);

            // メールオプション
            $mail_options = ['subject' => $subject, 'template' => 'mail.send'];

            // メール送信（管理者側）
            if ($user_register_mail_send_flag) {
                $mail_addresses = explode(',', Configs::getConfigsValue($configs, 'user_register_mail_send_address'));
                foreach ($mail_addresses as $mail_address) {
                    // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                    $this->sendMail($mail_address, $mail_options, ['content' => $mail_text], 'RegistersUsers');
                }
            }

            // メール送信（ユーザー側）
            if ($user_register_user_mail_send_flag) {
                $user_mailaddress = $user->email;
                if (!empty($user_mailaddress)) {
                    // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                    $this->sendMail($user_mailaddress, $mail_options, ['content' => $mail_text], 'RegistersUsers');
                }
            }
        }
    }

    /**
     * トークンを使った本登録の確定画面表示
     */
    public function confirmToken(Request $request)
    {
        // 設定の取得
        $configs = Configs::where('category', 'user_register')->get();

        // 仮登録機能OFFは、エラー画面へ
        if (!Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_flag')) {
            abort(403, '権限がありません');
        }
        $id = (string) $request->route('id');
        $token = (string) $request->route('token');

        // ユーザが存在しない場合、エラー画面へ
        $user = User::where('id', $id)->first();
        if (empty($user)) {
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['有効期限切れのため、そのURLはご利用できません。'],
            ]);
        }

        // dd($request->route('id'));
        // 項目のエラーチェック(トークンチェック)
        $validator = Validator::make(
            ['token' => $token],
            [
                'token' => [new CustomVali_TokenExists($user->add_token, $user->add_token_created_at)],
            ]
        );
        // getで日付形式エラーは表示しない（通常URLをコピペミス等でいじらなければエラーにならない想定）
        if ($validator->fails()) {
            return view('auth.register_error_messages', [
                'error_messages' => $validator->errors()->all(),
            ]);
        }

        // ユーザが利用不可の場合、エラー画面へ
        if ($user->status == \UserStatus::not_active) {
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['有効期限切れのため、そのURLはご利用できません。'],
            ]);
        }

        if ($user->status == \UserStatus::active) {
            // session()->flash('flash_message_for_header', '既に認証済みです。登録したログインID、パスワードでログインしてください。');
            // return redirect(RouteServiceProvider::HOME);
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['既にユーザ本登録済みです。'],
            ]);
        }

        // 表示テンプレートを呼び出す。
        return view('auth.register_confirm_token', [
            'id' => $id,
            'token' => $token,
        ]);
    }

    /**
     * 本登録トークン確認
     */
    public function storeToken(Request $request)
    {
        // 設定の取得
        $configs = Configs::get();

        // 仮登録機能OFFは、エラー画面へ
        if (!Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_flag')) {
            abort(403, '権限がありません');
        }
        $id = (string) $request->route('id');
        $token = (string) $request->route('token');

        // ユーザが存在しない場合、エラー画面へ
        $user = User::where('id', $id)->first();
        if (empty($user)) {
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['有効期限切れのため、そのURLはご利用できません。'],
            ]);
        }

        // dd($request->route('id'));
        // 項目のエラーチェック(トークンチェック)
        $validator = Validator::make(
            ['token' => $token],
            [
                'token' => [new CustomVali_TokenExists($user->add_token, $user->add_token_created_at)],
            ]
        );
        // getで日付形式エラーは表示しない（通常URLをコピペミス等でいじらなければエラーにならない想定）
        if ($validator->fails()) {
            return view('auth.register_error_messages', [
                'error_messages' => $validator->errors()->all(),
            ]);
        }

        // ユーザが利用不可の場合、エラー画面へ
        if ($user->status == \UserStatus::not_active) {
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['有効期限切れのため、そのURLはご利用できません。'],
            ]);
        }

        if ($user->status == \UserStatus::active) {
            // session()->flash('flash_message_for_header', '既に認証済みです。登録したログインID、パスワードでログインしてください。');
            // return redirect(RouteServiceProvider::HOME);
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['既にユーザ本登録済みです。'],
            ]);
        }

        // 登録完了
        $user->status = \UserStatus::active;
        $user->save();

        // 本登録時のメール送信
        $this->sendMailToActive($configs, $user);

        // 登録完了メッセージ。
        session()->flash('flash_message_for_header', Configs::getConfigsValue($configs, 'user_register_after_message'));

        // return redirect($this->redirectPath())->with('verified', true);
        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}

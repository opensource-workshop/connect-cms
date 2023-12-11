<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Models\Core\Configs;
use App\Models\Core\UsersColumnsSet;
use App\Models\Core\UsersRoles;
use App\Traits\ConnectCommonTrait;
use App\Traits\ConnectMailTrait;

use Carbon\Carbon;

use App\Plugins\Manage\UserManage\UsersTool;
use App\Utilities\Token\TokenUtils;
use App\Rules\CustomValiTokenExists;
use App\Providers\RouteServiceProvider;

use App\Enums\UserRegisterNoticeEmbeddedTag;
use App\Enums\UserStatus;
use App\Models\Core\Section;
use App\Models\Core\UserSection;

trait RegistersUsers
{
    use RedirectsUsers;
    use ConnectCommonTrait;
    use ConnectMailTrait;

    /**
     * Show the application registration form.
     * ユーザー登録画面表示（自動登録）
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm(Request $request)
    {
        // ユーザー登録の許可
        $user_register_enables = Configs::
            leftJoin('users_columns_sets', function ($join) {
                $join->on('users_columns_sets.id', 'configs.additional1');
            })
            ->where('configs.category', 'user_register')
            ->where('configs.name', 'user_register_enable')
            ->where('configs.value', '1')
            ->orderBy('users_columns_sets.display_sequence')
            ->get();

        $user_register_enable = $user_register_enables->first();
        // ユーザー登録の許可が１つもない場合(null)、画面表示は出来ないため、columns_set_id=0でもOK
        $columns_set_id_default = $user_register_enable ? $user_register_enable->additional1 : 0;

        $columns_set_id = $request->input('columns_set_id', $columns_set_id_default);

        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'general')
            ->orWhere(function ($query) use ($columns_set_id) {
                $query->where('category', 'user_register')
                    ->where('additional1', $columns_set_id);
            })
            // （全ての自動ユーザ登録設定で共通設定. additional1=all. 項目セット名とか）
            ->orWhere(function ($query) {
                $query->where('category', 'user_register')
                    ->where('additional1', 'all');
            })
            ->get();

        // ユーザ登録の権限チェック
        if ($this->isCan('admin_user')) {
            // ユーザ登録の権限があればOK
        // } elseif ($configs_array['user_register_enable'] != "1") {
        } elseif ($user_register_enables->isEmpty()) {
            // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
            abort(403);
        }

        // ユーザ登録が有効な項目セット
        $columns_sets = UsersColumnsSet::whereIn('id', $user_register_enables->pluck('additional1'))->orderBy('display_sequence')->get();

        // *** ユーザの追加項目
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumnsRegister($columns_set_id);
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects($columns_set_id);
        // カラムの登録データ
        $input_cols = null;

        // サイトテーマ詰込
        $tmp_configs = Configs::getSharedConfigs();
        $base_theme = Configs::getConfigsValue($tmp_configs, 'base_theme', null);
        $additional_theme = Configs::getConfigsValue($tmp_configs, 'additional_theme', null);
        $themes = [
            'css' => $base_theme,
            'js' => $base_theme,
            'additional_css' => $additional_theme,
            'additional_js' => $additional_theme,
        ];

        // フォームの初期値として空のユーザオブジェクトを渡す。
        return view('auth.register', [
            "user" => new User(),
            "configs" => $configs,
            'columns_set_id' => $columns_set_id,
            'columns_sets' => $columns_sets,
            'users_columns' => $users_columns,
            'users_columns_id_select' => $users_columns_id_select,
            'input_cols' => $input_cols,
            'themes' => $themes,
            'sections' => Section::orderBy('display_sequence')->get(),
            'user_section' => new UserSection(),
        ]);
    }

    /**
     * ユーザー登録画面 再表示（自動登録）
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function reShowRegistrationForm(Request $request)
    {
        // old()に全inputをセット
        $request->flash();

        return redirect(route('show_register_form') . "?columns_set_id=$request->columns_set_id");
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // 設定の取得
        // $configs = Configs::where('category', 'user_register')->get();
        // $configs = Configs::get();
        $configs = Configs::where('category', 'general')
            ->orWhere(function ($query) use ($request) {
                $query->Where('category', 'user_register')
                    ->Where('additional1', $request->columns_set_id);
            })
            ->get();

        // ユーザ登録の権限チェック
        //if (isset($user) && ($user->role == 1 || $user->role == 3)) {
        if ($this->isCan('admin_user')) {
            // ユーザ登録の権限があればOK
        } elseif (Configs::getConfigsValue($configs, 'user_register_enable') != "1") {
            // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
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

        // ユーザー自動登録（未ログイン、ユーザ管理者以外）の場合、.env のSELF_REGISTER_ROLE を元に権限登録する。
        // envの設定がなければ、自動ユーザ登録設定をもとに権限を登録する
        if (!Auth::user() || !$this->isCan('admin_user')) {
            $self_register_base_roles_env = '';
            if (config('connect.SELF_REGISTER_BASE_ROLES') !== null) {
                $self_register_base_roles_env = config('connect.SELF_REGISTER_BASE_ROLES');
            } else {
                $self_register_base_roles_env = Configs::getConfigsValue($configs, 'user_register_base_roles');
            }
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

        // ユーザー自動登録（未ログイン、ユーザ管理者以外）
        if (!Auth::user() || !$this->isCan('admin_user')) {
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
                $user->add_token_created_at = new Carbon();
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

        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath())->with('flash_message', 'ユーザ登録しました。続けて参加グループを設定してください。');
    }

    /**
     * メール本文取得
     */
    private function getMailContentsText($configs, $user)
    {
        return UsersTool::getMailContentsText($configs, $user);
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
            // メール件名
            $subject = Configs::getConfigsValue($configs, 'user_register_mail_subject');
            // メール本文
            $mail_format = Configs::getConfigsValue($configs, 'user_register_mail_format');

            // 埋め込みタグ
            $notice_embedded_tags = UsersTool::getNoticeEmbeddedTags($user);

            $subject = UserRegisterNoticeEmbeddedTag::replaceEmbeddedTags($subject, $notice_embedded_tags);
            $mail_text = UserRegisterNoticeEmbeddedTag::replaceEmbeddedTags($mail_format, $notice_embedded_tags);

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

        // 設定の取得
        $configs = Configs::where('category', 'user_register')->where('additional1', $user->columns_set_id)->get();

        // 仮登録機能OFFは、エラー画面へ
        if (!Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_flag')) {
            abort(403, '権限がありません');
        }

        // 項目のエラーチェック(トークンチェック)
        $validator = Validator::make(
            ['token' => $token],
            [
                'token' => [new CustomValiTokenExists($user->add_token, $user->add_token_created_at)],
            ]
        );
        // getで日付形式エラーは表示しない（通常URLをコピペミス等でいじらなければエラーにならない想定）
        if ($validator->fails()) {
            return view('auth.register_error_messages', [
                'error_messages' => $validator->errors()->all(),
            ]);
        }

        // ユーザが利用不可、仮削除の場合、エラー画面へ
        if ($user->status == UserStatus::not_active || $user->status == UserStatus::temporary_delete) {
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['有効期限切れのため、そのURLはご利用できません。'],
            ]);
        }

        if ($user->status == UserStatus::active || $user->status == UserStatus::pending_approval) {
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

        // 設定の取得
        // $configs = Configs::get();
        $configs = Configs::where('category', 'user_register')->where('additional1', $user->columns_set_id)->get();

        // 仮登録機能OFFは、エラー画面へ
        if (!Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_flag')) {
            abort(403, '権限がありません');
        }

        // 項目のエラーチェック(トークンチェック)
        $validator = Validator::make(
            ['token' => $token],
            [
                'token' => [new CustomValiTokenExists($user->add_token, $user->add_token_created_at)],
            ]
        );
        // getで日付形式エラーは表示しない（通常URLをコピペミス等でいじらなければエラーにならない想定）
        if ($validator->fails()) {
            return view('auth.register_error_messages', [
                'error_messages' => $validator->errors()->all(),
            ]);
        }

        // ユーザが利用不可、仮削除の場合、エラー画面へ
        if ($user->status == UserStatus::not_active || $user->status == UserStatus::temporary_delete) {
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['有効期限切れのため、そのURLはご利用できません。'],
            ]);
        }

        if ($user->status == UserStatus::active || $user->status == UserStatus::pending_approval) {
            // session()->flash('flash_message_for_header', '既に認証済みです。登録したログインID、パスワードでログインしてください。');
            // return redirect(RouteServiceProvider::HOME);
            // エラー画面へ
            return view('auth.register_error_messages', [
                'error_messages' => ['既にユーザ本登録済みです。'],
            ]);
        }

        // 登録完了
        if (Configs::getConfigsValue($configs, 'user_registration_require_approval')) {
            // 承認要のため承認待ち
            $user->status = UserStatus::pending_approval;
        } else {
            // 承認不要なので利用可能に
            $user->status = UserStatus::active;
        }
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

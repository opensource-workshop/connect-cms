<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
//use App\Providers\RouteServiceProvider;
use App\User;
//use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Controllers\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;
use App\Models\Core\Configs;
use App\Models\Core\UsersInputCols;
use App\Plugins\Manage\UserManage\UsersTool;
use App\Rules\CustomValiUserEmailUnique;
use Illuminate\Database\Eloquent\Collection;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    //protected $redirectTo = RouteServiceProvider::HOME;
    protected $redirectTo = '/manage/user';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // change: ユーザーの追加項目に対応
        // $validate_rule = [
        //     'name'     => 'required|string|max:255',
        //     'userid'   => 'required|max:255|unique:users',
        //     'email'    => 'nullable|email|max:255|unique:users',
        //     'password' => 'required|string|min:6|confirmed',
        //     'status'   => 'required',
        // ];
        $validator_array = [
            'column' => [
                'name'     => 'required|string|max:255',
                'userid'   => 'required|max:255|unique:users',
                'email'    => ['nullable', 'email', 'max:255', new CustomValiUserEmailUnique(null)],
                'password' => 'required|string|min:6|confirmed',
                'status'   => 'required',
            ],
            // 項目名
            'message' => [
                'name' => 'ユーザ名',
                'userid' => 'ログインID',
                'email' => 'eメール',
                'password' => 'パスワード',
                'status' => '状態',
                'user_register_requre_privacy' => '個人情報保護方針への同意',
            ]
        ];

        // ユーザ自動登録の場合（認証されていない）は、メールアドレスも必須にする。
        if (!Auth::user()) {
            // change: ユーザーの追加項目に対応
            // $validate_rule['email'] = 'required|string|email|max:255|unique:users';
            // $validate_rule['user_register_requre_privacy'] = 'required';
            $validator_array['column']['email'] = ['required', 'email', 'max:255', new CustomValiUserEmailUnique(null)];

            // bugfix: 個人情報保護方針への同意を求める場合、必須にする
            $user_register_requre_privacy = Configs::where('name', 'user_register_requre_privacy')->first();
            if (!empty($user_register_requre_privacy) && $user_register_requre_privacy->value == '1') {
                $validator_array['column']['user_register_requre_privacy'] = 'required';
            }
        }

        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();

        foreach ($users_columns as $users_column) {
            // バリデータールールをセット
            $validator_array = UsersTool::getValidatorRule($validator_array, $users_column, null);
        }

        // 入力値チェック
        // $validator = Validator::make($data, $validate_rule);
        $validator = Validator::make($data, $validator_array['column']);

        // 項目名
        // change: ユーザーの追加項目に対応
        // $validator->setAttributeNames([
        //     'name'                         => 'ユーザ名',
        //     'userid'                       => 'ログインID',
        //     'email'                        => 'eメールアドレス',
        //     'password'                     => 'パスワード',
        //     'user_register_requre_privacy' => '個人情報保護方針への同意',
        // ]);
        $validator->setAttributeNames($validator_array['message']);
        // dd($validator->errors());
        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // ユーザ自動登録の場合（認証されていない）、もしくはユーザ管理者以外は、トップページに遷移する。
        if (!Auth::user() || !Auth::user()->can('admin_user')) {
            $this->redirectTo = '/';
        }

        // 設定の取得
        $configs = Configs::get();

        $status = $this->userStatus($data, $configs);

        // ユーザ登録
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'userid' => $data['userid'],
            // change to laravel6.
            // 'password' => bcrypt($data['password']),
            'password' => Hash::make($data['password']),
            'status' => $status,
        ]);

        // ユーザーの追加項目の登録.
        // ----------------------------------
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();

        // users_input_cols 登録
        foreach ($users_columns as $users_column) {
            $value = "";
            if (!isset($data['users_columns_value'][$users_column->id])) {
                // 値なし
                $value = null;
            } elseif (is_array($data['users_columns_value'][$users_column->id])) {
                $value = implode(UsersTool::CHECKBOX_SEPARATOR, $data['users_columns_value'][$users_column->id]);
            } else {
                $value = $data['users_columns_value'][$users_column->id];
            }

            // データ登録フラグを見て登録
            $users_input_cols = new UsersInputCols();
            $users_input_cols->users_id = $user->id;
            $users_input_cols->users_columns_id = $users_column->id;
            $users_input_cols->value = $value;
            $users_input_cols->save();
        }

        if ($this->isCan('admin_user')) {
            // メールアドレス入力ありなら、メール送信画面へ
            if ($data['email']) {
                $this->redirectTo = '/manage/user/mail/' . $user->id;
            }
        }

        return $user;
    }

    /**
     * 登録するユーザーステータスを取得する
     *
     * @param array $data
     * @param Conllection　$configs 設定値
     * @return ユーザステータス
     */
    private function userStatus(array $data, Collection $configs) : int
    {
        // ユーザ管理権限があれば、画面からの値をそのまま使う
        if (Auth::user() && Auth::user()->can('admin_user')) {
            return $data['status'];
        }

        // ユーザ仮登録ON
        if (Configs::getConfigsValue($configs, 'user_register_temporary_regist_mail_flag') == 1) {
            return UserStatus::temporary;
        }

        // ユーザー登録に承認が必要な場合は、強制的にステータスを承認待ちにする
        // ただし、承認待ちより仮登録を優先する（仮登録でメールアドレスの正当性を担保した後に承認待ちにする）
        if (Configs::getConfigsValue($configs, 'user_registration_require_approval')) {
            return UserStatus::pending_approval;
        }

        // 自動登録が許可されている場合の状態は利用可能とする
        return UserStatus::active;
    }
}

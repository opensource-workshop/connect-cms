<?php

namespace app\Plugins\Mypage\ProfileMypage;

use App\Enums\EditType;
use App\Enums\UserColumnType;
use App\Models\Core\Section;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersInputCols;
use App\Models\Core\UserSection;
use App\Plugins\Manage\UserManage\UsersTool;
use App\Plugins\Mypage\MypagePluginBase;
use App\Rules\CustomValiLoginIdAndPasswordDoNotMatch;
use App\Rules\CustomValiUserEmailUnique;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * プロフィールマイページクラス
 * @see \app\Plugins\Manage\UserManage\UserManage to copy
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プロフィール
 * @package Controller
 * @plugin_title プロフィール
 * @plugin_desc 自分の情報を変更できます。
 */
class ProfileMypage extends MypagePluginBase
{
    /**
     * ページ初期表示(ユーザ変更画面表示)
     *
     * @method_title プロフィール変更
     * @method_desc パスワードやメールアドレスを変更できます。
     * @method_detail
     */
    public function index($request, $id = null)
    {
        // post ＆ URLのなかに'/mypage/profile/index'が含まれている場合、oldに値をセット。(optionテンプレート等で使用)
        // 入力エラー時はリダイレクトでget通信がくるので、その時は通さない
        if ($request->isMethod('post') && strpos($request->url(), '/mypage/profile/index') !== false) {
            // old()に全inputをセット
            $request->flash();
        }

        // ログインしているユーザー情報を取得
        $user = Auth::user();
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($user->columns_set_id);
        $users_columns = $users_columns->where('is_edit_my_page', EditType::ok);
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects($user->columns_set_id);
        // カラムの登録データ
        $input_cols = UsersTool::getUsersInputCols([$user->id]);

        // 画面呼び出し
        return view('plugins.mypage.profile.edit', [
            'themes'                  => $request->themes,
            "function"                => __FUNCTION__,
            "plugin_name"             => "profile",
            "id"                      => $user->id,
            "user"                    => $user,
            "users_columns"           => $users_columns,
            "users_columns_id_select" => $users_columns_id_select,
            "input_cols"              => $input_cols,
            'sections'                => Section::orderBy('display_sequence')->get(),
            'user_section'            => UserSection::where('user_id', $user->id)->firstOrNew(),
        ]);
    }

    /**
     * 更新
     */
    public function update($request, $id)
    {
        $user = User::where('id', $id)->first();

        // ユーザーのカラム
        $users_columns_all = UsersTool::getUsersColumns($user->columns_set_id);
        $users_columns = $users_columns_all->where('is_edit_my_page', EditType::ok);

        // 項目のエラーチェック
        $validator_array = [
            'column'  => [],
            'message' => [
                'name'         => UsersColumns::getLabelUserName($users_columns),
                'userid'       => UsersColumns::getLabelLoginId($users_columns),
                'email'        => UsersColumns::getLabelUserEmail($users_columns),
                'now_password' => '現在の' . UsersColumns::getLabelUserPassword($users_columns),
                'new_password' => '新しい' . UsersColumns::getLabelUserPassword($users_columns),
            ]
        ];

        foreach ($users_columns as $users_column) {
            if ($users_column->column_type == UserColumnType::user_name) {
                $base_rules = ['required', 'string', 'max:255'];
                $validator_array['column']['name'] = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column, $user->columns_set_id, $id);
            } elseif ($users_column->column_type == UserColumnType::login_id) {
                $base_rules = ['required', 'max:255', Rule::unique('users', 'userid')->ignore($id)];
                $validator_array['column']['userid'] = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column, $user->columns_set_id, $id);
            } elseif ($users_column->column_type == UserColumnType::user_email) {
                // $validator_array['column']['email'] = ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($id)];
                $base_rules = ['nullable', 'email', 'max:255', new CustomValiUserEmailUnique($request->columns_set_id, $id)];
                $validator_array['column']['email'] = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column, $user->columns_set_id, $id);
            } elseif ($users_column->column_type == UserColumnType::user_password) {
                // 入力があったら、ここで現在のパスワードチェック
                $validator_array['column']['now_password'] = [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        $is_pass = Hash::check($value, Auth::user()->password);
                        // nc2移行ユーザログイン対応 v1.0.0以前
                        $is_nc2_pass = Hash::check(md5($value), Auth::user()->password);
                        if (!$is_nc2_pass) {
                            // nc2移行ユーザログイン対応 v1.0.0よりあと
                            $is_nc2_pass = md5($value) === Auth::user()->password;
                        }
                        // どちらもNGなら現在パスワード間違い
                        if ($is_pass == false && $is_nc2_pass == false) {
                            $fail(':attributeが違います。');
                        }
                    },
                ];

                // ログインID
                $userid = $request->userid ?? $user->userid;

                $validator_array['column']['new_password'] = [
                    'nullable',
                    'string',
                    'min:6',
                    'confirmed',
                    new CustomValiLoginIdAndPasswordDoNotMatch($userid, UsersColumns::getLabelLoginId($users_columns_all)),
                ];
            } elseif ($users_column->column_type == UserColumnType::created_at) {
                // チェックしない
            } elseif ($users_column->column_type == UserColumnType::updated_at) {
                // チェックしない
            } else {
                // バリデータールールをセット
                $validator_array = UsersTool::getValidatorRule($validator_array, $users_column, $user->columns_set_id, $id);
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);

        $validator->sometimes("now_password", 'required', function ($input) {
            // 新しいパスワード又は、新しいパスワードの確認に入力あったら、上記の現在のパスワード必須
            return $input->new_password || $input->new_password_confirmation;
        });
        $validator->sometimes("new_password", 'required', function ($input) {
            // 現在のパスワードに入力あったら、上記の新しいパスワード必須
            return $input->now_password;
        });

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        foreach ($users_columns as $users_column) {
            if ($users_column->column_type == UserColumnType::user_name) {
                $user->name = $request->name;
            } elseif ($users_column->column_type == UserColumnType::login_id) {
                $user->userid = $request->userid;
            } elseif ($users_column->column_type == UserColumnType::user_email) {
                $user->email = $request->email;
            } elseif ($users_column->column_type == UserColumnType::user_password) {
                // パスワードの入力があれば、更新
                if (!empty($request->new_password)) {
                    // change to laravel6.
                    // $update_array['password'] = bcrypt($request->new_password);
                    $user->password = Hash::make($request->new_password);
                }
            } elseif ($users_column->column_type == UserColumnType::created_at) {
                // 入力なし
            } elseif ($users_column->column_type == UserColumnType::updated_at) {
                // 入力なし
            } else {
                // users_input_cols 登録
                $value = "";
                if (!isset($request->users_columns_value[$users_column->id])) {
                    // 値なし
                    $value = null;
                } elseif (is_array($request->users_columns_value[$users_column->id])) {
                    $value = implode(UsersTool::CHECKBOX_SEPARATOR, $request->users_columns_value[$users_column->id]);
                } else {
                    $value = $request->users_columns_value[$users_column->id];
                }

                // 所属型は個別のテーブルに書き込む
                if ($users_column->column_type === UserColumnType::affiliation) {
                    // 値無しは所属情報を削除
                    if (empty($value)) {
                        UserSection::where('user_id', $user->id)->delete();
                    } else {
                        UserSection::updateOrCreate(
                            ['user_id' => $user->id],
                            ['section_id' => $value]
                        );
                        // users_input_cols には　名称を設定する
                        $value = Section::find($value)->name;
                    }
                }

                // ユーザーの追加項目
                // 値無しは削除
                if (empty($value)) {
                    UsersInputCols::where('users_id', $user->id)->where('users_columns_id', $users_column->id)->delete();
                } else {
                    UsersInputCols::updateOrCreate(
                        ['users_id' => $user->id, 'users_columns_id' => $users_column->id],
                        ['value' => $value]
                    );
                }
            }
        }

        // ユーザデータの更新
        $user->save();

        $message = '更新しました。';

        // 更新後は初期画面へ
        return redirect('mypage/profile')->with('flash_message', $message);
    }
}

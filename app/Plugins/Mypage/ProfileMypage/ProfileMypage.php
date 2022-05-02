<?php

namespace app\Plugins\Mypage\ProfileMypage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\User;

use App\Plugins\Mypage\MypagePluginBase;

/**
 * プロフィールマイページクラス
 * @see \app\Plugins\Manage\UserManage\UserManage to copy
 *
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
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ログインしているユーザー情報を取得
        $user = Auth::user();

        // 画面呼び出し
        return view('plugins.mypage.profile.edit', [
            'themes'                => $request->themes,
            "function"              => __FUNCTION__,
            "plugin_name"           => "profile",
            "id"                    => $user->id,
            "user"                  => $user,
        ]);
    }

    /**
     * 更新
     */
    public function update($request, $id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            // 入力があったら、ここで現在のパスワードチェック
            'now_password' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!\Hash::check($value, Auth::user()->password)) {
                        $fail(':attributeが違います。');
                    }
                },
            ],
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $validator->setAttributeNames([
            'email' => 'eメール',
            'now_password' => '現在のパスワード',
            'new_password' => '新しいパスワード',
        ]);

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

        // 更新内容の配列
        $update_array = [
            'email' => $request->email,
        ];

        // パスワードの入力があれば、更新
        if (!empty($request->new_password)) {
            // change to laravel6.
            // $update_array['password'] = bcrypt($request->new_password);
            $update_array['password'] = Hash::make($request->new_password);
        }

        // ユーザデータの更新
        User::where('id', $id)
            ->update($update_array);

        // 更新後は初期画面へ
        return redirect('mypage/profile');
    }
}

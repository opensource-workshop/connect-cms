{{--
 * 強制ログアウト画面テンプレート
--}}

{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

    {{-- 登録後メッセージ表示 --}}
    @include('plugins.common.flash_message')

    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">強制ログアウトを設定します。<br />全ユーザ、次回の画面操作でログアウトされ、ログイン画面に誘導されます。</span>

            <div class="text-center mt-3">
                {{-- 強制ログアウトボタン --}}
                <form action="{{url('/manage/user/forceLogoutSubmit')}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('強制ログアウトを設定します。\nよろしいですか？')"><i class="fas fa-check"></i> 強制ログアウトを設定する。</button>
                </form>
            </div>

        </div>
    </div>

</div>

@endsection

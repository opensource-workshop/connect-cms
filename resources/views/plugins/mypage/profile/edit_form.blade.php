{{--
 * copy by resources\views\auth\registe_form.blade.php
--}}

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message')

<form action="{{url('/')}}/mypage/profile/update/{{$id}}" class="form-horizontal" method="POST">
    {{ csrf_field() }}

    <div class="form-group row">
        <label for="email" class="col-md-4 col-form-label text-md-right">eメールアドレス</label>

        <div class="col-md-8">
            <input id="email" type="text" class="form-control @if ($errors->has('email')) border-danger @endif" name="email" value="{{ old('email', $user->email) }}" placeholder="メールアドレスを入力します。">
            @include('plugins.common.errors_inline', ['name' => 'email'])
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">現在のパスワード</label>

        <div class="col-md-8">
            <input type="password" class="form-control @if ($errors->has('now_password')) border-danger @endif" name="now_password" autocomplete="new-password" placeholder="現在のパスワードを入力します。">
            @include('plugins.common.errors_inline', ['name' => 'now_password'])
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">新しいパスワード</label>

        <div class="col-md-8">
            <input type="password" class="form-control @if ($errors->has('new_password')) border-danger @endif" name="new_password" autocomplete="new-password" placeholder="新しいパスワードを入力します。">
            @include('plugins.common.errors_inline', ['name' => 'new_password'])
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">新しいパスワードの確認</label>

        <div class="col-md-8">
            <input type="password" class="form-control @if ($errors->has('new_password_confirmation')) border-danger @endif" name="new_password_confirmation" placeholder="新しいパスワードと同じものを入力します。">
            @include('plugins.common.errors_inline', ['name' => 'new_password_confirmation'])
        </div>
    </div>

    <div class="form-group row text-center">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>
                更新
            </button>
        </div>
    </div>
</form>


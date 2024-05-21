{{--
 * ログイン画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ログイン・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if (Auth::user())
    <div>
        {{Auth::user()->name}}
    </div>

    <div class="mt-3">
        <a href="{{url('/')}}/logput" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-primary" role="button" aria-pressed="true">ログアウト</a>
    </div>
@else

<form class="" method="POST" action="{{url('/')}}/login">
    {{ csrf_field() }}

    <div class="form-group row">
        <label for="userid" class="col-md-4 col-form-label text-md-right">ログインID</label>

        <div class="col-md-6">
            <input id="userid" type="text" class="form-control" name="userid" value="" required="" autofocus="">
        </div>
    </div>
    <div class="form-group row">
        <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>

        <div class="col-md-6">
            <input id="password" type="password" class="form-control" name="password" required="">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6 offset-md-4">
            <div class="custom-control custom-checkbox mr-sm-2">
                <input type="checkbox" name="remember" class="custom-control-input" id="remember">
                <label class="custom-control-label" for="remember" title="ログイン状態を維持するチェックボックス">ログイン状態を維持する。</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary" dusk="login-button">
                <i class="fas fa-check"></i> ログイン
            </button>

            <a class="btn btn-link" href="{{url('/')}}/password/reset" dusk="login_password_reset">
                パスワードを忘れた場合。
            </a>
        </div>
    </div>
</form>
@endif

@endsection

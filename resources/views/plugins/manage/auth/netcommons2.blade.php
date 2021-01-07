{{--
 * 外部認証>NetCommons2認証
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.auth.auth_manage_tab')
    </div>

    <div class="card-body">
        <form name="form_auth" id="form_auth" class="form-horizontal" method="post" action="{{url('/')}}/manage/auth/netcommons2Update">
            {{ csrf_field() }}

            {{-- サイトURL --}}
            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">サイトURL</label>
                    <input type="text" name="auth_netcomons2_site_url" value="{{$config->additional1}}" class="form-control">
                    <small class="form-text text-muted">NetCommons2 のURL（最後の / はナシ）</small>
                </div>
            </div>

            {{-- site_key --}}
            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">site_key</label>
                    <input type="text" name="auth_netcomons2_site_key" value="{{$config->additional2}}" class="form-control">
                </div>
            </div>

            {{-- Salt --}}
            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">Salt</label>
                    <input type="text" name="auth_netcomons2_salt" value="{{$config->additional3}}" class="form-control">
                </div>
            </div>

            {{-- デフォルトで追加するオリジナル権限 --}}
            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">デフォルトで追加するオリジナル権限</label>
                    <input type="text" name="auth_netcomons2_add_role" value="{{$config->additional4}}" class="form-control">
                </div>
            </div>

            {{-- 管理者操作用パスワード --}}
            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">管理者操作用パスワード</label>
                    <input type="text" name="auth_netcomons2_admin_password" value="{{$config->additional5}}" class="form-control">
                </div>
            </div>

            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>
    </div>
</div>

@endsection

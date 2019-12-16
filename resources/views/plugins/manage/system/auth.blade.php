{{--
 * システム管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 外部認証
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- 機能選択タブ --}}
@include('plugins.manage.system.system_tab')

<form name="form_auth" id="form_auth" class="form-horizontal" method="post" action="/manage/system/updateAuth">
    {{ csrf_field() }}

    <div class="card">
        <div class="card-body">

            <div class="form-group">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (empty($config) || $config->value == "")
                        <input type="radio" value="" id="auth_method" name="auth_method" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="" id="auth_method" name="auth_method" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="auth_method">外部認証を使用しない。</label>
                </div>
            </div>
            <div class="form-group mb-0">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (!empty($config) && $config->value == 'netcommons2')
                        <input type="radio" value="netcommons2" id="netcommons2" name="auth_method" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="netcommons2" id="netcommons2" name="auth_method" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="netcommons2">NetCommons2 認証連携を使用する。</label>
                </div>
            </div>

            {{-- サイトURL --}}
            <div class="form-group row mb-0">
                <div class="col-1"></div>
                <div class="col-11">
                    <label class="col-form-label">サイトURL</label>
                    <input type="text" name="auth_netcomons2_site_url" value="{{$config->additional1}}" class="form-control">
                    <small class="form-text text-muted">NetCommons2 のURL（最後の / はナシ）</small>
                </div>
            </div>

            {{-- site_key --}}
            <div class="form-group row mb-0">
                <div class="col-1"></div>
                <div class="col-10 col-sm-6 col-md-4">
                    <label class="col-form-label">site_key</label>
                    <input type="text" name="auth_netcomons2_site_key" value="{{$config->additional2}}" class="form-control">
                </div>
            </div>

            {{-- Salt --}}
            <div class="form-group row">
                <div class="col-1"></div>
                <div class="col-10 col-sm-6 col-md-4">
                    <label class="col-form-label">Salt</label>
                    <input type="text" name="auth_netcomons2_salt" value="{{$config->additional3}}" class="form-control">
                </div>
            </div>

            {{-- デフォルトで追加するオリジナル権限 --}}
            <div class="form-group row">
                <div class="col-1"></div>
                <div class="col-11">
                    <label class="col-form-label">デフォルトで追加するオリジナル権限</label>
                    <input type="text" name="auth_netcomons2_add_role" value="{{$config->additional4}}" class="form-control">
                </div>
            </div>

            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>

        </div>
    </div>
</form>

@endsection

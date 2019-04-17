{{--
 * サイト管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- 機能選択タブ --}}
@include('plugins.manage.site.site_manage_tab')

<div class="panel panel-default">
<div class="panel-body">

    <form action="/manage/site/update" method="POST">
    {{csrf_field()}}

        {{-- サイト名 --}}
        <div class="form-group">
            <label class="control-label">サイト名</label>
            <input type="text" name="base_site_name" value="{{$configs["base_site_name"]}}" class="form-control">
            <div class="help-block">サイト名（各ページで上書き可能 ※予定）</div>
        </div>

        {{-- 背景色 --}}
        <div class="form-group">
            <label class="control-label">背景色</label>
            <input type="text" name="base_background_color" value="{{$configs["base_background_color"]}}" class="form-control">
            <div class="help-block">画面の基本の背景色（各ページで上書き可能）</div>
        </div>

        {{-- ヘッダーの背景色 --}}
        <div class="form-group">
            <label class="control-label">ヘッダーの背景色</label>
            <input type="text" name="base_header_color" value="{{$configs["base_header_color"]}}" class="form-control">
            <div class="help-block">画面の基本のヘッダー背景色（各ページで上書き可能）</div>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal">更新</button>
        </div>
    </form>
</div>
</div>

@endsection

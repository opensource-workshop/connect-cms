{{--
 * ページエラー設定のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.site.site_manage_tab')

</div>
<div class="card-body">

    <form action="/manage/site/savePageError" method="POST">
    {{csrf_field()}}

        {{-- 403 --}}
        <div class="form-group">
            <label class="col-form-label">IPアドレス制限などで権限がない場合の表示ページ</label>
            <input type="text" name="page_permanent_link_403" value="{{$page_errors["page_permanent_link_403"]->value}}" class="form-control">
        </div>

        {{-- 404 --}}
        <div class="form-group">
            <label class="col-form-label">指定ページがない場合の表示ページ</label>
            <input type="text" name="page_permanent_link_404" value="{{$page_errors["page_permanent_link_404"]->value}}" class="form-control">
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </form>
</div>
</div>

@endsection

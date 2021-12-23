{{--
 * サイト管理（サイト設計書）のテンプレート
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

    <form action="{{url('/')}}/manage/site/downloadDocument" method="POST" class="">
        {{ csrf_field() }}

        {{-- 各 大エリアのブラウザ幅 --}}
        <div class="form-group">
            <label class="col-form-label">出力する内容</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        <input name="browser_width_header" value="100%" type="checkbox" class="custom-control-input" id="browser_width_header" checked="checked">
                        <label class="custom-control-label" for="browser_width_header" id="label_browser_width_header">ヘッダーエリア</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group text-center">
            <button type="reset" class="btn btn-secondary mr-2"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 出力</span></button>
        </div>
    </form>
</div>
</div>

@endsection

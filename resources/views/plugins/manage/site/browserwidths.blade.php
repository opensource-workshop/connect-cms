{{--
 * サイト管理（ブラウザ幅）のテンプレート
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

    <form action="{{url('/')}}/manage/site/saveLayout" method="POST" class="">
        {{ csrf_field() }}

        {{-- 各 大エリアのブラウザ幅 --}}
        <div class="form-group">
            <label class="col-form-label">ブラウザ幅の100％で表示する設定</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($browser_widths["browser_width_header"]) &&
                            isset($browser_widths["browser_width_header"]["value"]) &&
                            $browser_widths["browser_width_header"]["value"] == "100%")
                            <input name="browser_width_header" value="100%" type="checkbox" class="custom-control-input" id="browser_width_header" checked="checked">
                        @else
                            <input name="browser_width_header" value="100%" type="checkbox" class="custom-control-input" id="browser_width_header">
                        @endif
                        <label class="custom-control-label" for="browser_width_header" id="label_browser_width_header">ヘッダーエリア</label>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="custom-control custom-checkbox">
                        @if(isset($browser_widths["browser_width_center"]) &&
                            isset($browser_widths["browser_width_center"]["value"]) &&
                            $browser_widths["browser_width_center"]["value"] == "100%")
                            <input name="browser_width_center" value="100%" type="checkbox" class="custom-control-input" id="browser_width_center" checked="checked">
                        @else
                            <input name="browser_width_center" value="100%" type="checkbox" class="custom-control-input" id="browser_width_center">
                        @endif
                        <label class="custom-control-label" for="browser_width_center" id="label_browser_width_center">センターエリア（左、メイン、右）</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($browser_widths["browser_width_footer"]) &&
                            isset($browser_widths["browser_width_footer"]["value"]) &&
                            $browser_widths["browser_width_footer"]["value"] == "100%")
                            <input name="browser_width_footer" value="100%" type="checkbox" class="custom-control-input" id="browser_width_footer" checked="checked">
                        @else
                            <input name="browser_width_footer" value="100%" type="checkbox" class="custom-control-input" id="browser_width_footer">
                        @endif
                        <label class="custom-control-label" for="browser_width_footer" id="label_browser_width_footer">フッター</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">ブラウザ幅100％で表示するものをチェック</small>
        </div>

        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/site/layout')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
        </div>
    </form>
</div>
</div>

@endsection

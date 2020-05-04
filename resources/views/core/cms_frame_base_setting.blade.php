{{--
 * CMSフレーム基礎画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- 設定系メニューがデザインに引きずられて画面が不完全になるのを防ぐための措置 --}}
<style type="text/css">
<!--
#frame-{{$frame->id}} {
    background: #ffffff;
    color: #000000;
    max-height: 100%;
}
-->
</style>
{{-- フレームが配置ページでない場合の注意 --}}
@if($frame->page_id != $page_id)
<script type="text/javascript">
    // ツールチップ
    $(function () {
        // 有効化
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
<div class="card-header bg-warning">
    配置されたページと異なるページです。<span class="fas fa-info-circle" data-toggle="tooltip" title="" data-original-title="設定を変更すると、配置されたページ以下のページに影響があります。"></span>
</div>
@endif
<div class="frame-setting">
<div class="frame-setting-menu">
    <nav class="navbar {{$frame->getNavbarExpand()}} navbar-light bg-light">
        <span class="{{$frame->getNavbarBrand()}}">設定メニュー</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                {{-- プラグイン側のフレームメニュー --}}
                @yield("core.cms_frame_edit_tab_$frame->id")

                {{-- コア側のフレームメニュー --}}
                @include('core.cms_frame_edit_tab')
            </ul>
        </div>
    </nav>
</div>

@if ($frame->frame_design == 'none')
<div class="card-body frame-setting-body" style="padding: 0; clear: both;">
@else
<div class="card-body frame-setting-body">
@endif
    @yield("plugin_setting_$frame->id")
</div>
</div>

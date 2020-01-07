{{--
 * CMSフレーム基礎画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
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

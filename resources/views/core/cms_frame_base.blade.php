{{--
 * CMSフレーム基礎画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
@if ($frame->frame_design == 'none')
<div class="card-body clearfix p-0">
@else
<div class="card-body">
@endif

    @yield("plugin_contents_$frame->id")

</div>

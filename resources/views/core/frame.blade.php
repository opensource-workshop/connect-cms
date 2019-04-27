{{--
 * CMSフレーム画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}

<div class="modal-body">

	@if ($frame->frame_design == 'none')
	<div class="panel panel-{{$frame->frame_design}}" style="-webkit-box-shadow: none; box-shadow: none; background-color: transparent;">
	@else
	<div class="panel panel-{{$frame->frame_design}}">
	@endif

	    <div class="panel-heading">
	        {{$frame->frame_title}}
	    </div>

	    <div class="panel-body">

{{-- 機能選択タブ --}}
@include('plugins.user.contents.default.contents_edit_tab')



            @include('core.cms_frame_edit')
	    </div>
	</div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
</div>

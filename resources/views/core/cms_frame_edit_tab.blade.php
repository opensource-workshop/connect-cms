{{--
 * CMSフレーム編集タブ
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- コア側のフレームメニュー --}}

{{-- フレーム編集 --}}
@if (app('request')->input('action') == 'frame_setting')
    <li role="presentation" class="active">
@else
    <li role="presentation">
@endif
{{-- <a href="{{URL::to($page->permanent_link)}}/?action=frame_setting&frame_id={{ $frame->id }}#{{ $frame->id }}">フレーム編集</a></li> --}}
{{-- <a href="{{URL::to('/')}}/plugin/blogs/frame_setting/{{$page->id}}/{{ $frame->id }}#{{ $frame->id }}">フレーム編集</a></li> --}}
<a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_setting/{{$page->id}}/{{ $frame->id }}#{{ $frame->id }}">フレーム編集</a></li>

{{-- フレーム削除 --}}
@if (app('request')->input('action') == 'frame_delete')
    <li role="presentation" class="active">
@else
    <li role="presentation">
@endif
{{-- <a href="{{URL::to($page->permanent_link)}}/?action=frame_delete&frame_id={{ $frame->id }}#{{ $frame->id }}">フレーム削除</a></li> --}}
<a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_delete/{{$page->id}}/{{ $frame->id }}#{{ $frame->id }}">フレーム削除</a></li>

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
<li class="nav-item">
@if (app('request')->input('action') == 'frame_setting')
    <a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_setting/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">フレーム編集</a></li>
@else
    <a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_setting/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フレーム編集</a></li>
@endif

{{-- フレーム削除 --}}
<li class="nav-item">
@if (app('request')->input('action') == 'frame_delete')
    <a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_delete/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">フレーム削除</a></li>
@else
    <a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_delete/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フレーム削除</a></li>
@endif

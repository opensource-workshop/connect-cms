{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 --}}
@yield('content_frame_edit_tab')
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">{{$frame->plugin_name_full}}選択</a></li>
@else
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">{{$frame->plugin_name_full}}選択</a></li>
@endif

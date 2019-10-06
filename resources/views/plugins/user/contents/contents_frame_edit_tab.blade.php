{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if ($action == 'edit' || $action == '')
    <li class="nav-item"><a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">編集</a></li>
@else
    <li class="nav-item"><a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">編集</a></li>
@endif

@if ($action == 'show')
    <li class="nav-item"><a href="{{url('/')}}/plugin/contents/show/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">データ削除</a></li>
@else
    <li class="nav-item"><a href="{{url('/')}}/plugin/contents/show/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">データ削除</a></li>
@endif

@if ($action == 'listBuckets')
    <li class="nav-item"><a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">表示コンテンツ選択</a></li>
@else
    <li class="nav-item"><a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">表示コンテンツ選択</a></li>
@endif

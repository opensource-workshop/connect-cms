{{--
 * 編集画面tabテンプレート
 *
 * @author 
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
@if ($action == 'editBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">設定変更</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">設定変更</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/reservations/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">新規作成</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/reservations/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">新規作成</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/reservations/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">表示する施設予約を選択</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/reservations/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">表示する施設予約を選択</a>
    </li>
@endif
@if ($action == 'editBucketsRoles' || $action == '')
    <li class="nav-item"><a href="{{url('/')}}/plugin/reservations/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">権限設定</a></li>
@else
    <li class="nav-item"><a href="{{url('/')}}/plugin/reservations/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">権限設定</a></li>
@endif

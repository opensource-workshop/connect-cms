{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
 --}}
@if ($action == 'editBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">設定変更</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/cabinets/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">設定変更</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">新規作成</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/cabinets/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">新規作成</a>
    </li>
@endif
{{--
@if ($action == 'editView')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">表示設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/cabinets/editView/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">表示設定</a>
    </li>
@endif
--}}
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">キャビネット選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/cabinets/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">キャビネット選択</a>
    </li>
@endif
@if ($action == 'editBucketsRoles' || $action == '')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">権限設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/cabinets/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">権限設定</a>
    </li>
@endif

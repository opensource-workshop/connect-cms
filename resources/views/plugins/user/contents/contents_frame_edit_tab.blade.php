{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if ($action == 'show')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">データ削除</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/contents/show/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">データ削除</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">表示コンテンツ選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">表示コンテンツ選択</a>
    </li>
@endif
@if ($action == 'editBucketsRoles' || $action == '')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">権限設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/contents/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">権限設定</a>
    </li>
@endif

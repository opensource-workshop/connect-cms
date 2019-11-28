{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if ($action == 'editBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">設定変更</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">設定変更</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">新規作成</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">新規作成</a>
    </li>
@endif
@if ($action == 'listCategories')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listCategories/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">カテゴリ</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listCategories/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">カテゴリ</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">表示ブログ選択</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">表示ブログ選択</a>
    </li>
@endif
@if ($action == 'editBucketsRoles' || $action == '')
    <li class="nav-item"><a href="{{url('/')}}/plugin/blogs/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">権限設定</a></li>
@else
    <li class="nav-item"><a href="{{url('/')}}/plugin/blogs/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">権限設定</a></li>
@endif

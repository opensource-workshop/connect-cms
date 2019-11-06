{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if ($action == 'editBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/editBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">ブログ設定変更</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/editBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">ブログ設定変更</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/createBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">ブログ新規作成</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/createBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">ブログ新規作成</a>
    </li>
@endif
@if ($action == 'listCategories')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listCategories/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">カテゴリ</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listCategories/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">カテゴリ</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">表示ブログ選択</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/blogs/listBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">表示ブログ選択</a>
    </li>
@endif

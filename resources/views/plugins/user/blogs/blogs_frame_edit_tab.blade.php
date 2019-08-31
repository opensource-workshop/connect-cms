{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if ($action == 'editBlog')
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/blogs/editBlog/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">ブログ設定変更</a></li>
@else
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/blogs/editBlog/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">ブログ設定変更</a></li>
@endif
@if ($action == 'createBlog')
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/blogs/createBlog/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">ブログ新規作成</a></li>@else
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/blogs/createBlog/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">ブログ新規作成</a></li>
@endif
@if ($action == 'datalist')
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/blogs/datalist/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">表示ブログ選択</a></li>
@else
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/blogs/datalist/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">表示ブログ選択</a></li>
@endif

{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
<li role="presentation"@if ($action == 'editBlog') class="active"@endif><a href="{{url('/')}}/plugin/blogs/editBlog/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">ブログ設定変更</a></li>
<li role="presentation"@if ($action == 'createBlog') class="active"@endif><a href="{{url('/')}}/plugin/blogs/createBlog/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">ブログ新規作成</a></li>
<li role="presentation"@if ($action == 'datalist') class="active"@endif><a href="{{url('/')}}/plugin/blogs/datalist/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">表示ブログ選択</a></li>

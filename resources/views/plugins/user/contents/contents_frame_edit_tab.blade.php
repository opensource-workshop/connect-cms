{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
<li role="presentation"@if ($action == 'edit' || $action == '') class="active"@endif><a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">編集</a></li>
<li role="presentation"@if ($action == 'edit_show') class="active"@endif><a href="{{url('/')}}/plugin/contents/edit_show/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">データ削除</a></li>
<li role="presentation"@if ($action == 'datalist') class="active"@endif><a href="{{url('/')}}/plugin/contents/datalist/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">表示コンテンツ選択</a></li>

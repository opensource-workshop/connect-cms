{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
<li role="presentation"@if (Request::get('frame_action') == 'editBlog') class="active"@endif><a href="{{$page->permanent_link}}?frame_action=editBlog&frame_id={{$frame->id}}#{{$frame->id}}">ブログ設定変更</a></li>
<li role="presentation"@if (Request::get('frame_action') == 'createBlog') class="active"@endif><a href="{{$page->permanent_link}}?frame_action=createBlog&frame_id={{$frame->id}}#{{$frame->id}}">ブログ新規作成</a></li>
<li role="presentation"@if (Request::get('frame_action') == 'datalist') class="active"@endif><a href="{{$page->permanent_link}}?frame_action=datalist&frame_id={{$frame->id}}#{{$frame->id}}">表示ブログ選択</a></li>

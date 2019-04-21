{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
<ul class="nav nav-tabs">
    <li role="presentation"@if (Request::get('action') == 'edit' || Request::get('action') == '') class="active"@endif><a href="{{$page->permanent_link}}?action=edit&frame_id={{$frame_id}}&tab=frame#{{$frame_id}}">編集</a></li>
    <li role="presentation"@if (Request::get('action') == 'edit_show') class="active"@endif><a href="{{$page->permanent_link}}?action=edit_show&frame_id={{$frame_id}}&tab=destroy#{{$frame_id}}">データ削除</a></li>
    <li role="presentation"@if (Request::get('action') == 'datalist') class="active"@endif><a href="{{$page->permanent_link}}?action=datalist&frame_id={{$frame_id}}&tab=datalist#{{$frame_id}}">データリスト</a></li>
</ul>

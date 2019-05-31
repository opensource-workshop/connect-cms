{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
<li role="presentation"@if ($action == 'editOpac') class="active"@endif><a href="{{url('/')}}/plugin/opacs/editOpac/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">OPAC設定変更</a></li>
<li role="presentation"@if ($action == 'createOpac') class="active"@endif><a href="{{url('/')}}/plugin/opacs/createOpac/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">OPAC新規作成</a></li>
<li role="presentation"@if ($action == 'datalist') class="active"@endif><a href="{{url('/')}}/plugin/opacs/datalist/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">表示OPAC選択</a></li>

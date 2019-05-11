{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 --}}
<li role="presentation"@if ($action == 'edit' || $action == '') class="active"@endif><a href="{{url('/')}}/plugin/forms/edit/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">項目設定</a></li>

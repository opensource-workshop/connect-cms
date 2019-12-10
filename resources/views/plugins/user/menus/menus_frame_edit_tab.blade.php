{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
 --}}
@if ($action == 'select')
    <li class="nav-item"><a href="{{url('/')}}/plugin/menus/select/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">ページ選択</a></li>
@else
    <li class="nav-item"><a href="{{url('/')}}/plugin/menus/select/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">ページ選択</a></li>
@endif

{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
 --}}
@if ($action == 'select')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">ページ選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/menus/select/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">ページ選択</a>
    </li>
@endif

@if ($action == 'editFrameRoles' || $action == 'saveFrameRoles')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">権限設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/menus/editFrameRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">権限設定</a>
    </li>
@endif

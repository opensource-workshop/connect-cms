{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 --}}
@if ($action == 'editColumn' || $action == 'editColumnDetail'  || $action == '')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">項目設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/forms/editColumn/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">項目設定</a>
    </li>
@endif
@if ($action == 'editBuckets' || $action == '')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">フォーム設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/forms/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フォーム設定</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">フォーム作成</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フォーム作成</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">フォーム選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フォーム選択</a>
    </li>
@endif

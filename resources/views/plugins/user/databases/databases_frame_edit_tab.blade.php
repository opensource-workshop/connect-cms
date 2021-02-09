{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベースプラグイン
--}}
@if ($action == 'editColumn' || $action == 'editColumnDetail'  || $action == '')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">項目設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/editColumn/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">項目設定</a>
    </li>
@endif
@if ($action == 'editView')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">表示設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/editView/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">表示設定</a>
    </li>
@endif
@if ($action == 'editBuckets' || $action == '')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">DB設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">DB設定</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">DB作成</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">DB作成</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">DB選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">DB選択</a>
    </li>
@endif
@if ($action == 'import')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">CSVインポート</span></span>
    </li>
@endif
@if ($action == 'editBucketsRoles' || $action == 'saveBucketsRoles')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">権限設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">権限設定</a>
    </li>
@endif
@if ($action == 'editBucketsMails' || $action == 'saveBucketsMails')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">メール設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/editBucketsMails/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">メール設定</a>
    </li>
@endif

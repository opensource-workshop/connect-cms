{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
--}}
@if ($action == 'editBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">設定変更</span></span>
    </li>
</li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/opacs/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">設定変更</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">新規作成</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/opacs/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">新規作成</a>
    </li>
@endif
@if ($action == 'settingOpacFrame')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">表示設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/opacs/settingOpacFrame/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">表示設定</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">OPAC選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/opacs/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">OPAC選択</a>
    </li>
@endif

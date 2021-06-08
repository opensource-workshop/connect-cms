{{--
 * 編集画面tabテンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/editFacilities/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'editFacilities' ? 'active' : '' }}">施設設定</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/editColumns/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'editColumns' ? 'active' : '' }}">項目設定</a>
</li>

@if ($action == 'editColumnDetail')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">項目詳細設定</span></span>
    </li>
@endif

<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'editBuckets' ? 'active' : '' }}">設定変更</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'createBuckets' ? 'active' : '' }}">新規作成</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'listBuckets' ? 'active' : '' }}">表示コンテンツ選択</a>
</li>
{{-- TODO:権限機能が解析しきれていない為、一旦非表示＠2019/12/17 --}}
{{-- <li class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'editBucketsRoles' || $action == '' ? 'active' : '' }}">権限設定</a>
</li> --}}

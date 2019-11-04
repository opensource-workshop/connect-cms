{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}
@if ($action == 'editBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/editBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">開館カレンダー設定変更</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/editBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">開館カレンダー設定変更</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/createBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">新規作成</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/createBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">新規作成</a>
    </li>
@endif
@if ($action == 'listPatterns')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/listPatterns/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">パターン</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/listPatterns/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">パターン</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/listBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">選択</a>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/openingcalendars/listBuckets/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">選択</a>
    </li>
@endif

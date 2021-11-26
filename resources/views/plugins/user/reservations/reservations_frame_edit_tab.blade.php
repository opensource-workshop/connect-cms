{{--
 * 編集画面tabテンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/choiceFacilities/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'choiceFacilities' ? 'active' : '' }}">施設設定</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'editBuckets' ? 'active' : '' }}">設定変更</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'createBuckets' ? 'active' : '' }}">新規作成</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'listBuckets' ? 'active' : '' }}">施設予約選択</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/reservations/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'editBucketsRoles' ? 'active' : '' }}">権限設定</a>
</li>
<li role="presentation" class="nav-item">
    <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/editBucketsMails/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link {{ $action == 'editBucketsMails' ? 'active' : '' }}">メール設定</a>
</li>

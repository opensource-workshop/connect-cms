{{--
 * 設定画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@if ($action == 'editBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">設定変更</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/learningtasks/editBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">設定変更</a>
    </li>
@endif
@if ($action == 'createBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">新規作成</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/learningtasks/createBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">新規作成</a>
    </li>
@endif
@if ($action == 'editMail')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">メール設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/learningtasks/editMail/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">メール設定</a>
    </li>
@endif
@if ($action == 'listCategories')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">カテゴリ</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/learningtasks/listCategories/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">カテゴリ</a>
    </li>
@endif
@if ($action == 'listBuckets')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">課題管理選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/learningtasks/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">課題管理選択</a>
    </li>
@endif
{{-- delete: 編集者やモデレータで科目を登録・編集は使われていないため一旦削除。もしモデレータによる登録・編集が必要になったら復活させる。
@if ($action == 'editBucketsRoles' || $action == '')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">権限設定</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/learningtasks/editBucketsRoles/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">権限設定</a>
    </li>
@endif
--}}

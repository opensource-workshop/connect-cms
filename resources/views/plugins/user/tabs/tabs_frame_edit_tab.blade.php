{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category タブ・プラグイン
 --}}
@if ($action == 'select')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">フレーム選択</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/tabs/select/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フレーム選択</a>
    </li>
@endif

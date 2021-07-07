{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コードスタディ・プラグイン
 --}}
@if ($action == 'viewDownload')
    <li role="presentation" class="nav-item">
        <span class="nav-link"><span class="active">学習結果ダウンロード</span></span>
    </li>
@else
    <li role="presentation" class="nav-item">
        <a href="{{url('/')}}/plugin/codestudies/viewDownload/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">学習結果ダウンロード</a>
    </li>
@endif

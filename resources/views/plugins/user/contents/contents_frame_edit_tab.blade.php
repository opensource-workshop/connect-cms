{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@php
    // タブに紐づくURLとタブ名を配列に保持
    $arr_tab_name['show'] = 'データ削除';
    $arr_tab_name['listBuckets'] = '表示コンテンツ選択';
    $arr_tab_name['editBucketsRoles'] = '権限設定';
@endphp
{{-- ループしてタブを生成 --}}
@foreach ($arr_tab_name as $url => $tab_name)
<li role="presentation" class="nav-item">
    @if ($action == $url)
        <span class="nav-link"><span class="active">{{ $tab_name }}</span></span>
    @else
        <a href="{{url('/')}}/plugin/contents/{{ $url }}/{{ $page->id }}/{{ $frame->id }}#frame-{{ $frame->id }}" class="nav-link">{{ $tab_name }}</a>
    @endif
</li>
@endforeach
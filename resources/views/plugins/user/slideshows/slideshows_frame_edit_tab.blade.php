{{--
 * 設定画面TAB
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
--}}
@php
    $arr_tab_name['editItem'] = '項目設定';
    $arr_tab_name['editBuckets'] = 'スライドショー設定';
    $arr_tab_name['createBuckets'] = 'スライドショー作成';
    $arr_tab_name['listBuckets'] = '表示コンテンツ選択';
@endphp
{{-- ループしてタブを生成 --}}
@foreach ($arr_tab_name as $url => $tab_name)
    <li role="presentation" class="nav-item">
        @if ($action == $url)
            <span class="nav-link"><span class="active">{{ $tab_name }}</span></span>
        @else
            <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/{{ $url }}/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">{{ $tab_name }}</a>
        @endif
    </li>
@endforeach
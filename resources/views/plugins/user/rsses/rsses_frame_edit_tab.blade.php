{{--
 * 設定画面TAB
 *
 * @author horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category RSS・プラグイン
--}}
@php
    $arr_tab_name['editUrl'] = '取得元Url設定';
    $arr_tab_name['editBuckets'] = 'RSS設定';
    $arr_tab_name['createBuckets'] = 'RSS作成';
    $arr_tab_name['listBuckets'] = '表示RSS選択';
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
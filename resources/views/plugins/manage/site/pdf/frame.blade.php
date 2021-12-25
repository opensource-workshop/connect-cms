{{--
 * サイト管理（サイト設計書）のフレーム設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">フレーム設定</h2>

<br />
<h4>フレーム設定（基本情報）</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 25%;">ページ名</th>
        <th class="doc_th" style="width: 25%;">フレームタイトル</th>
        <th class="doc_th" style="width: 20%;">プラグイン</th>
        <th class="doc_th" style="width: 9%;">ヘッダ</th>
        <th class="doc_th" style="width: 6%;">左</th>
        <th class="doc_th" style="width: 6%;">右</th>
        <th class="doc_th" style="width: 9%;">フッタ</th>
    </tr>
    @php
        $break_row = true;
        $count = 0;
    @endphp
    @foreach($frames as $frame)
        @php
            if ($break_row) {
                $count = count($frames->where('page_id', $frame->page_id));
            }
        @endphp
        <tr nobr="true">
            @if ($break_row)
                @if ($count > 1) <td rowspan="{{$count}}">{{$frame->page_name}}</td>
                @else <td>{{$frame->page_name}}</td>
                @endif
                @php
                    $break_row = false;
                @endphp
            @endif
            <td>{{$frame->frame_title}}</td>
            <td>{{$frame->plugin_name}}</td>
            @if ($frame->area_id == 0) <td>〇</td> @else <td></td> @endif
            @if ($frame->area_id == 1) <td>〇</td> @else <td></td> @endif
            @if ($frame->area_id == 3) <td>〇</td> @else <td></td> @endif
            @if ($frame->area_id == 4) <td>〇</td> @else <td></td> @endif
        </tr>
        @php
            $count--;
            if ($count == 0) {
                $break_row = true;
            }
        @endphp
    @endforeach
</table>
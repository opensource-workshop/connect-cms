{{--
 * サイト管理（サイト設計書）のフレームデザイン設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>フレーム設定（デザイン情報）</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 25%;">ページ名</th>
        <th class="doc_th" style="width: 25%;">フレームタイトル</th>
        <th class="doc_th" style="width: 12%;">デザイン</th>
        <th class="doc_th" style="width: 8%;">幅</th>
        <th class="doc_th" style="width: 16%;">テンプレート</th>
        <th class="doc_th" style="width: 14%;">class</th>
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
            <td>{{$frame->frame_design}}</td>
            @if ($frame->frame_col == 0) <td>100％</td> @else <td>{{$frame->frame_col}}</td> @endif
            <td>{{$frame->template}}</td>
            <td>{{$frame->classname}}</td>
        </tr>
        @php
            $count--;
            if ($count == 0) {
                $break_row = true;
            }
        @endphp
    @endforeach
</table>
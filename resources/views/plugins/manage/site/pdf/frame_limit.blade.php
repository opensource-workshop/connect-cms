{{--
 * サイト管理（サイト設計書）のフレーム制限設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>フレーム設定（制限情報）</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th" style="width: 25%;">ページ名</th>
        <th class="doc_th" style="width: 25%;">フレームタイトル</th>
        <th class="doc_th" style="width: 8%;">初期<br />非表示</th>
        <th class="doc_th" style="width: 4%;">対象</th>
        <th class="doc_th" style="width: 6%;">公開<br />設定</th>
        <th class="doc_th" style="width: 16%;">公開From</th>
        <th class="doc_th" style="width: 16%;">公開To</th>
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
        <tr>
            @if ($break_row)
                @if ($count > 1) <td rowspan="{{$count}}">{{$frame->page_name}}</td>
                @else <td>{{$frame->page_name}}</td>
                @endif
                @php
                    $break_row = false;
                @endphp
            @endif
            <td>{{$frame->frame_title}}</td>
            <td>{{$frame->default_hidden}}</td>
            <td>{{$frame->page_only}}</td>
            <td>{{$frame->content_open_type}}</td>
            <td>{{$frame->content_open_date_from}}</td>
            <td>{{$frame->content_open_date_to}}</td>
        </tr>
        @php
            $count--;
            if ($count == 0) {
                $break_row = true;
            }
        @endphp
    @endforeach
</table>
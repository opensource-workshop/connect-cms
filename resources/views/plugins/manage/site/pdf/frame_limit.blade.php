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
    <tr nobr="true">
        <th class="doc_th" style="width: 25%;">ページ名</th>
        <th class="doc_th" style="width: 25%;">フレームタイトル</th>
        <th class="doc_th" style="width: 10%;">初期<br />非表示</th>
        <th class="doc_th" style="width: 4%;">対象</th>
        <th class="doc_th" style="width: 6%;">公開<br />設定</th>
        <th class="doc_th" style="width: 15%;">公開From</th>
        <th class="doc_th" style="width: 15%;">公開To</th>
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
            @if ($frame->default_hidden == 1) <td>〇</td> @else <td></td> @endif
            @if ($frame->area_id == 2) <td></td>
            @elseif ($frame->page_only == 0) <td>全</td>
            @elseif ($frame->page_only == 1) <td>表</td>
            @elseif ($frame->page_only == 2) <td>非</td>
            @else <td></td>
            @endif
            @if ($frame->content_open_type == 1) <td>公</td>
            @elseif ($frame->content_open_type == 2) <td>非</td>
            @elseif ($frame->content_open_type == 3) <td>限</td>
            @else <td></td>
            @endif
            @if ($frame->content_open_type == 3)
            <td>{{$frame->content_open_date_from}}</td>
            <td>{{$frame->content_open_date_to}}</td>
            @else
            <td></td>
            <td></td>
            @endif
        </tr>
        @php
            $count--;
            if ($count == 0) {
                $break_row = true;
            }
        @endphp
    @endforeach
</table>
<p style="font-size: 10px;">
※ 対象はメイン以外の共通ページの設定です。内容は以下。<br />
全：対象ページ全てで表示する。　表：このページのみ表示する。　非：このページのみ表示しない。
</p>
<p style="font-size: 10px;">
※ 公開限定は公開・限定設定です。内容は以下。<br />
公：公開　非：非公開　限：限定公開
</p>

{{--
 * サイト管理（サイト設計書）のフレームデータ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>フレーム設定（データ情報）</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th" style="width: 25%;">ページ名</th>
        <th class="doc_th" style="width: 25%;">フレームタイトル</th>
        <th class="doc_th" style="width: 20%;">プラグイン</th>
        <th class="doc_th" style="width: 30%;">バケツ</th>
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
            <td>{{$frame->plugin_name}}</td>
            <td>{{$frame->bucket_name}}</td>
        </tr>
        @php
            $count--;
            if ($count == 0) {
                $break_row = true;
            }
        @endphp
    @endforeach
</table>
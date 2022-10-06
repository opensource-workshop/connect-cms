{{--
 * サイト管理（サイト設計書）のリンクチェック -リンクチェック一覧のテンプレート
 *
 * @author horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">リンクチェック</h2>
<br />
<h4>リンクチェックデータ一覧</h4>
@if ($link_error_list_src)
    <h5>エラー画像一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%; font-size: 12px;">No.</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">ページ名</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">フレーム名</th>
            <th class="doc_th" style="width: 65%; font-size: 12px;">src</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($link_error_list_src as $val)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$val['page_name']}}</td><td>{{$val['frame_title']}}</td><td>{{$val['url']}}</td>
            </tr>
        @endforeach
    </table>
@endif
@if ($link_error_list_href)
    <h5>エラーリンク一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%; font-size: 12px;">No.</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">ページ名</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">フレーム名</th>
            <th class="doc_th" style="width: 65%; font-size: 12px;">href</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($link_error_list_href as $val)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$val['page_name']}}</td><td>{{$val['frame_title']}}</td><td>{{$val['url']}}</td>
            </tr>
        @endforeach
    </table>
@endif

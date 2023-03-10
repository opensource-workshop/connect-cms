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
<h2 style="text-align: center; font-size: 28px;">{{ __('messages.content_url_broken_link_check') }}</h2>
<br />
<h4>{{ __('messages.content_url_broken_link_check') }}一覧</h4>
<h5>
    - 固定記事プラグインで設定されているすべてのリンクに対してHTTPレスポンスチェックを行い、HTTPステータスがNGのものを一覧で出力しています。<br>
    - 代表的なNGのHTTPステータスの凡例：
    <ul>
        <li>403：未認証等、アクセス権がないURLです。</li>
        <li>404：リンク切れしているURLです。</li>
        <li>50x：システムエラーが発生しているURLです。</li>
    </ul>
    - 尚、HTTPステータスが下記のものは正常リンクとして出力から除外しています。
    <ul>
        <li>20X：成功レスポンス系（OK）</li>
        <li>30X：リダイレクト系</li>
   </ul>
</h5>
@if ($link_error_list_src)
    <h5>エラー画像一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%; font-size: 12px;">No.</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">ページ名</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">フレーム名</th>
            <th class="doc_th" style="width: 50%; font-size: 12px;">src</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">HTTPステータス</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($link_error_list_src as $val)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$val['page_name']}}</td><td>{{$val['frame_title']}}</td><td>{{$val['url']}}</td><td>{{$val['http_status']}}</td>
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
            <th class="doc_th" style="width: 50%; font-size: 12px;">href</th>
            <th class="doc_th" style="width: 15%; font-size: 12px;">HTTPステータス</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($link_error_list_href as $val)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$val['page_name']}}</td><td>{{$val['frame_title']}}</td><td>{{$val['url']}}</td><td>{{$val['http_status']}}</td>
            </tr>
        @endforeach
    </table>
@endif

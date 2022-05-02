{{--
 * サイト管理（サイト設計書）のテーマ管理 - ユーザ・テーマのテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">テーマ管理</h2>

<br />
<h4>ユーザ・テーマ</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 50%;">テーマ名</th>
        <th class="doc_th" style="width: 50%;">ディレクトリ名</th>
    </tr>
    @foreach($dirs as $dir)
    <tr nobr="true">
        <td>{{array_key_exists('theme_name', $dir) ? $dir['theme_name'] : ''}}</td>
        <td>{{array_key_exists('dir', $dir) ? $dir['dir'] : ''}}</td>
    </tr>
    @endforeach
</table>
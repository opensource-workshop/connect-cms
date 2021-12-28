{{--
 * サイト管理（サイト設計書）の多言語設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>多言語設定</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>多言語設定の使用</td>
        @if ($configs->firstWhere('name', 'language_multi_on')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
</table>

@foreach ($configs->where('category', 'language')->sortBy('id') as $language)
    @if ($loop->first)
        <br />
        <h4>設定済み言語</h4>
        <table border="0" class="table_css">
            <tr nobr="true">
                <th class="doc_th">言語</th>
                <th class="doc_th">URL</th>
            </tr>
    @endif
        <tr nobr="true">
            <td>{{$language->value}}</td>
            <td>{{$language->additional1}}</td>
        </tr>
    @if ($loop->last)
        </table>
    @endif
@endforeach

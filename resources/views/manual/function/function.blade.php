{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

@foreach ($dusks->groupBy('category') as $category)
    【{{ManualCategory::getDescription($category[0]->category)}}】<br />
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 30%;">機能名</th>
            <th class="doc_th" style="width: 70%;">機能概要</th>
        </tr>
        @foreach ($category->where('method_name', 'index') as $plugin)
        <tr nobr="true">
            <td style="width: 30%;">{{$plugin->plugin_title}}</td>
            <td style="width: 70%;">{{str_replace('<br />', "\n", $plugin->plugin_desc)}}</td>
        </tr>
        @endforeach
        {{-- 開発中などの説明を追加する。 --}}
        {!!$category[0]->getInsertion('category', 'function_foot_pdf')!!}
    </table>
    <br /><br />
@endforeach

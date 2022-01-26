{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

ここでは、Connect-CMS の【{{$category->category}}】について説明します。
<h2 style="text-align: center; font-size: 28px;">【{{$category->category}}のプラグイン一覧】</h2>
<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 20%;">プラグイン名</th>
        <th class="doc_th" style="width: 80%;">プラグイン概要</th>
    </tr>
    @foreach($plugins as $plugin)
    <tr nobr="true">
        <td>{{$plugin->plugin_title}}</td>
        <td>{!!$plugin->plugin_desc!!}</td>
    </tr>
    @endforeach
</table>
<br />
<br />

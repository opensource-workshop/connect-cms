{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<h2 style="text-align: center; font-size: 28px;">【{{$plugin->plugin_title}}プラグイン】</h2>
<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 20%;">機能名</th>
        <th class="doc_th" style="width: 80%;">機能概要</th>
    </tr>
    @foreach($methods as $method)
    <tr nobr="true">
        <td>{{$method->method_title}}</td>
        <td>{!!$method->method_desc!!}</td>
    </tr>
    @endforeach
</table>
<br />
<br />

{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<h3 style="text-align: center; font-size: 24px;">【{{$plugin->plugin_title}}】</h3>

Connect-CMS の【{{$plugin->plugin_title}}】プラグインについて説明します。<br />
{!!$plugin->plugin_desc!!}<br />
{!!$plugin->getInsertionPdf('plugin', 'desc')!!}

{{-- level に応じたメソッドの振り分け --}}
@php
$prints = array();

$methods_print = null;
$methods_online = null;

if (empty($level)) {
    $prints = ['機能一覧' => $methods];
} else {
    if ($methods->where('level', $level)->isNotEmpty()) {
        $prints['機能一覧'] = $methods->where('level', $level);
    }
    if ($methods->where('level', '!=', $level)->isNotEmpty()) {
        $prints['機能一覧（オンラインマニュアル参照）'] = $methods->where('level', '!=', $level);
    }
}
@endphp

@foreach($prints as $title => $print_methods)
<h3 style="text-align: center; font-size: 20px;"><u>{{$title}}</u></h3>
<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 20%;">機能名</th>
        <th class="doc_th" style="width: 80%;">機能概要</th>
    </tr>
    @foreach($print_methods as $method)
    <tr nobr="true">
        <td>{{$method->method_title}}</td>
        <td>{!!$method->method_desc!!}</td>
    </tr>
    @endforeach
</table>
@endforeach
{{-- 差し込み --}}
{!!$plugin->getInsertionPdf('plugin', 'foot')!!}
<br />
<br />

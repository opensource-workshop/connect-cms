{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

{{-- level に応じたプラグインの振り分け --}}
@php
$prints = array();

$plugins_print = null;
$plugins_online = null;

if (empty($level)) {
    $prints = ['プラグイン' => $plugins];
} else {
    if ($plugins->where('level', $level)->isNotEmpty()) {
        $prints['プラグイン'] = $plugins->where('level', $level);
    }
    if ($plugins->where('level', '!=', $level)->isNotEmpty()) {
        $prints['プラグイン（オンラインマニュアル参照）'] = $plugins->where('level', '!=', $level);
    }
}
@endphp

<h2 style="text-align: center; font-size: 28px;">【{{ManualCategory::getDescription($category->category)}}】</h2>

Connect-CMS の【{{ManualCategory::getDescription($category->category)}}】カテゴリについて説明します。<br />
カテゴリの中には、プラグイン（大項目）があり、プラグインの中に、それぞれの機能（小項目）があります。<br />
{{ManualCategory::getDescription($category->category)}}カテゴリのプラグイン一覧です。<br />

@foreach($prints as $title => $print_plugins)
<h3 style="text-align: center; font-size: 24px;"><u>{{$title}}</u></h3>
<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 20%;">プラグイン名</th>
        <th class="doc_th" style="width: 80%;">プラグイン概要</th>
    </tr>
    @foreach($print_plugins as $plugin)
    <tr nobr="true">
        <td>{{$plugin->plugin_title}}</td>
        <td>{!!$plugin->plugin_desc!!}</td>
    </tr>
    @endforeach
</table>
@endforeach
<br />
<br />

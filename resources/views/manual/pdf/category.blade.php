{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<h2 style="text-align: center; font-size: 28px;">【{{ManualCategory::getDescription($category->category)}}】カテゴリ</h2>

Connect-CMS の【{{ManualCategory::getDescription($category->category)}}】カテゴリについて説明します。<br />
カテゴリの中には、プラグインがあり、プラグインとは、メニューの大項目になります。<br />
プラグインの中に、それぞれの機能があります。
<h3 style="text-align: center; font-size: 24px;"><u>プラグイン一覧</u></h3>
{{ManualCategory::getDescription($category->category)}}カテゴリのプラグイン一覧になります。<br />
各プラグインには、個別の機能があります。<br />
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

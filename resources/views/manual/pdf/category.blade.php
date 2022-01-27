{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<h2 style="text-align: center; font-size: 28px;">【{{ManualCategory::getDescription($category->category)}}】</h2>

Connect-CMS の【{{ManualCategory::getDescription($category->category)}}】カテゴリについて説明します。<br />
カテゴリの中には、プラグイン（大項目）があり、プラグインの中に、それぞれの機能（小項目）があります。
<h3 style="text-align: center; font-size: 24px;"><u>プラグイン一覧</u></h3>
{{ManualCategory::getDescription($category->category)}}カテゴリのプラグイン一覧です。<br />
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

{{--
 * 表示画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 感染症数値集計プラグイン(covid)
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@php
    $option_blade_path = 'plugins.user.covids.default.select_option';
@endphp

<div class="alert alert-primary">
    @if (isset($target_date))
        対象日付：{{$target_date}}
    @endif
</div>

<form action="{{url('/')}}/plugin/covids/index/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    {{csrf_field()}}
    <div class="form-group row mb-3">
        <div class="col-sm-4">
            {{-- 日別状況表 --}}
            @include('plugins.user.covids.default.covids_view_type_select')
        </div>
        <div class="col-sm-4">
            <select class="form-control" name="target_date" onchange="javascript:submit(this.form);">
                <option value="">日付</option>
                @foreach ($covid_report_days as $covid_report_day)
                    @if ($covid_report_day->target_date == $target_date)
                        <option value="{{$covid_report_day->target_date}}" selected class="text-white bg-primary">{{$covid_report_day->target_date}}</option>
                    @else
                        <option value="{{$covid_report_day->target_date}}">{{$covid_report_day->target_date}}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-sm-4">
            {{-- 表示件数 --}}
            @include('plugins.user.covids.default.covids_view_count')
        </div>
    </div>
</form>

<div id="target" style="height: 700px;"></div>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    // パッケージのロード
    google.charts.load('current', {packages: ['corechart']});
    // ロード完了まで待機
    google.charts.setOnLoadCallback(drawChart);

    // コールバック関数の実装
    function drawChart() {
        // データの準備
/*
        var data = google.visualization.arrayToDataTable([
            ['国', 'アメリカ', 'ブラジル','日本'],
            ['06-10',  500 , 3000 , 3100],
            ['06-11',  500 , 3000 , 3800],
            ['06-12',  500 , 3000 , 2900],
            ['06-13',  500 , 3000 , 2500],
            ['06-14',  500 ,    0 , 2300],
            ['06-15',  500 ,    0 , 2100],
            ['06-16',  500 ,    0 , 1900]
        ]);
*/
        {{-- グラフデータ --}}
        @include('plugins.user.covids.default.covids_graph_data')

        // オプション設定
        var options = {
            @if ($view_type == '' || $view_type == 'graph_confirmed')
                title: '感染者推移グラフ',
            @elseif ($view_type == '' || $view_type == 'graph_deaths')
                title: '死亡者推移グラフ',
            @elseif ($view_type == '' || $view_type == 'graph_recovered')
                title: '回復者推移グラフ',
            @elseif ($view_type == '' || $view_type == 'graph_active')
                title: '感染中推移グラフ',
            @elseif ($view_type == '' || $view_type == 'graph_fatality_rate_moment')
                title: '致死率(計算日)推移グラフ',
            @elseif ($view_type == '' || $view_type == 'graph_fatality_rate_estimation')
                title: '致死率(予測)推移グラフ',
            @elseif ($view_type == '' || $view_type == 'graph_deaths_estimation')
                title: '死亡者数(予測)推移グラフ',
            @elseif ($view_type == '' || $view_type == 'graph_active_rate')
                title: 'Active率推移グラフ',
            @endif
            chartArea: {left: 100, width:'70%'}
        };

        // インスタンス化と描画
        var chart = new google.visualization.ComboChart(document.getElementById('target'));
        chart.draw(data, options);
    }
</script>

<div class="mt-3" role="alert">
    <small>※ このプラグインでは、Google社の Google Charts サービスを使用してグラフを表示しています。</small>
</div>

@endsection

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

<div class="table-responsive">
    <table class="table text-nowrap">
        <tr>
            <th>国/地域</th>
            <th>感染者数</th>
            <th>死亡者数</th>
            <th>回復者数</th>
            <th>感染中</th>
            <th>致死率<br />(計算日)</th>
            <th>致死率<br />(予測)</th>
            <th>死亡者数<br />予測</th>
            <th>Active率</th>
        </tr>
        @foreach ($covid_daily_reports as $covid_daily_report)
        <tr>
            <td>{{$covid_daily_report->country_region}}</td>
            <td>{{number_format($covid_daily_report->total_confirmed)}}</td>
            <td>{{number_format($covid_daily_report->total_deaths)}}</td>
            <td>{{number_format($covid_daily_report->total_recovered)}}</td>
            <td>{{number_format($covid_daily_report->total_active)}}</td>
            <td>{{$covid_daily_report->case_fatality_rate_moment}}％</td>
            <td>{{$covid_daily_report->case_fatality_rate_estimation}}％</td>
            <td>{{number_format($covid_daily_report->deaths_estimation)}}</td>
            <td>{{$covid_daily_report->active_rate}}％</td>
        </tr>
        @endforeach
    </table>
</div>

<div class="mt-3" role="alert">
    <small>※ このプラグインでは、Google社の Google Charts サービスを使用してグラフを表示しています。</small>
</div>

@endsection

var data = google.visualization.arrayToDataTable([
@foreach($covid_daily_reports as $covid_daily_report_key => $covid_daily_cols)
@if($loop->first)
['{{$covid_daily_report_key}}'@foreach($covid_daily_cols as $covid_daily_col),'{{$covid_daily_col}}'@endforeach
],
@else
['{{$covid_daily_report_key}}'@foreach($covid_daily_cols as $covid_daily_col),{{$covid_daily_col}}@endforeach],
@endif
@endforeach
]);

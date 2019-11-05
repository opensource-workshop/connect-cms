{{--
 * 開館カレンダー画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}

{{$openingcalendar_frame->openingcalendar_name}}<br />
{{$openingcalendar_frame->openingcalendar_sub_name}}<br />


<script type="text/javascript">
    function prev() {
        if ($("#calendar{{$frame_id}} .active").attr('data-prev') == "off") {
            $("#calendar_prev_link{{$frame_id}}").addClass("disabled");
        }
        else {
            $("#calendar_next_link{{$frame_id}}").removeClass("disabled");
            $("#calendar_prev_link{{$frame_id}}").removeClass("disabled");
        }
        $("#calendar{{$frame_id}}").carousel('prev');
        $("#view_ym_str{{$frame_id}}").text($("#calendar{{$frame_id}} .active").attr('data-prevmonth'));
    }
    function next() {
        if ($("#calendar{{$frame_id}} .active").attr('data-next') == "off") {
            $("#calendar_next_link{{$frame_id}}").addClass("disabled");
        }
        else {
            $("#calendar_next_link{{$frame_id}}").removeClass("disabled");
            $("#calendar_prev_link{{$frame_id}}").removeClass("disabled");
        }
        $("#calendar{{$frame_id}}").carousel('next');
        $("#view_ym_str{{$frame_id}}").text($("#calendar{{$frame_id}} .active").attr('data-nextmonth'));
    }

</script>
<a href="javascript:prev();" class="btn btn-primary @if ($default_disabled['prev'] == 'off') disabled @endif" id="calendar_prev_link{{$frame_id}}">
  <i class="fas fa-chevron-circle-left"></i>
</a>
<span id="view_ym_str{{$frame_id}}">{{$view_ym_str}}</span>
<a href="javascript:next();" class="btn btn-primary @if ($default_disabled['next'] == 'off') disabled @endif" id="calendar_next_link{{$frame_id}}">
  <i class="fas fa-chevron-circle-right"></i>
</a>

{{-- カレンダー --}}
<div id="calendar{{$frame_id}}" class="carousel slide" data-ride="carousel" data-interval=false data-wrap=false>
    <div class="carousel-inner">
        @foreach ($calendars as $calendar_ym => $dates)
        <div class="carousel-item @if($calendar_ym == $view_ym) active @endif" data-prev="{{$view_months[$calendar_ym]["data-prev"]}}" data-next="{{$view_months[$calendar_ym]["data-next"]}}" data-prevmonth="{{$view_months[$calendar_ym]["data-prevmonth"]}}" data-nextmonth="{{$view_months[$calendar_ym]["data-nextmonth"]}}">
            <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach (['日', '月', '火', '水', '木', '金', '土'] as $dayOfWeek)
                    <th>{{ $dayOfWeek }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach ($dates as $date)
                @if ($date->dayOfWeek == 0)
                <tr>
                @endif
                    <td
                    @if ($date->format("Y-m") != $calendar_ym)
                        class="bg-secondary"
                    @else
                        @if($view_days[$date->format("Y-m")][$date->format("d")])
                            style="background-color:{{$patterns[$view_days[$date->format("Y-m")][$date->format("d")]]}}"
                        @endif
                    @endif
                    >
                       {{ $date->day }}
                   </td>
                @if ($date->dayOfWeek == 6)
                </tr>
                @endif
            @endforeach
            </tbody>
            </table>

            {{-- パターン --}}
            <div class="d-md-table cc-table-set">
                @foreach($view_months_patterns[$calendar_ym] as $view_pattern)
                    <dl class="d-none d-md-table-row" style="font-size:90%;">
                        <dl class="d-md-table-cell"><span style="color:{{$view_pattern[0]->color}}">■</span></dl>
                        <dl class="d-md-table-cell">{{$view_pattern[0]->pattern}}</dl>
                        <dl class="d-md-table-cell">（{{$view_pattern[0]->caption}}）</dl>
                        @if (count($view_pattern) > 1)
                            <dl class="d-md-table-cell ml-2"><span style="color:{{$view_pattern[1]->color}}">■</span></dl>
                            <dl class="d-md-table-cell">{{$view_pattern[1]->pattern}}</dl>
                            <dl class="d-md-table-cell">（{{$view_pattern[0]->caption}}）</dl>
                        @endif
                    </dl>
                @endforeach
            </div>

        </div>
        @endforeach
    </div>
</div>

{{-- パターン --}}
{{--
<div class="d-md-table cc-table-set">
@foreach($patterns_chunks as $patterns_chunk)
    <dl class="d-none d-md-table-row" style="font-size:90%;">
        <dl class="d-md-table-cell"><span style="color:{{$patterns_chunk[0]->color}}">■</span></dl>
        <dl class="d-md-table-cell">{{$patterns_chunk[0]->pattern}}</dl>
        <dl class="d-md-table-cell">（{{$patterns_chunk[0]->caption}}）</dl>
        @if (count($patterns_chunk) > 1)
            <dl class="d-md-table-cell ml-2"><span style="color:{{$patterns_chunk[1]->color}}">■</span></dl>
            <dl class="d-md-table-cell">{{$patterns_chunk[1]->pattern}}</dl>
            <dl class="d-md-table-cell">（{{$patterns_chunk[0]->caption}}）</dl>
        @endif
    </dl>
@endforeach
</div>
--}}

@can("role_article")
    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/openingcalendars/edit/{{$page->id}}/{{$frame_id}}'"><i class="far fa-edit"></i> 編集</button>
@endcan

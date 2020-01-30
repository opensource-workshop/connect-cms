{{--
 * 施設予約データ表示画面（週）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

    {{-- カレンダーヘッダ部 --}}
    <br>

    {{-- メッセージエリア --}}
    @if ($message)
        <div class="alert alert-info mt-2">
            <i class="fas fa-exclamation-circle"></i>{{ $message }}
        </div>
    @endif

    <div class="row">
        <div class="col-12 clearfix">
            <div class="float-left">
                <div class="list-group list-group-horizontal">
                    {{-- 前週ボタン --}}
                    <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->subDay(7)->format('Ymd') }}#frame-{{$frame->id}}" class="list-group-item btn btn-light d-flex align-items-center">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    {{-- 当月表示 --}}
                    <a class="list-group-item h5 d-flex align-items-center">
                        {{ $carbon_target_date->year }}年 {{ $carbon_target_date->month }}月
                    </a>
                    {{-- 翌週ボタン --}}
                    <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->addDay(7)->format('Ymd') }}#frame-{{$frame->id}}" class="list-group-item btn btn-light d-flex align-items-center">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            </div>
            <div class="float-right col-sm-5">
                {{-- 当日へボタン --}}
                <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ Carbon::today()->format('Ymd') }}#frame-{{$frame->id}}" class="list-group-item btn btn-light rounded-pill">
                    今日へ<br>({{ Carbon::today()->format('Y年n月j日') }})
                </a>
            </div>
        </div>
    </div>
    <br>
    {{-- 登録している施設分ループ --}}
    @foreach ($calendars as $facility_name => $calendar_details)

        {{-- 施設名 --}}
        <span class="h5">＜{{ $facility_name }}＞</span>

        {{-- カレンダーデータ部 --}}
            <table class="table table-bordered cc_responsive_table" style="table-layout:fixed;">
                <thead>
                    {{-- カレンダーヘッダ部の曜日を表示 --}}
                    <tr>
                        @foreach ($calendar_details['calendar_cells'] as $cell)
                            {{-- 日曜なら赤文字、土曜なら青文字 --}}
                            <th class="text-center bg-light{{ $cell['date']->dayOfWeek == DayOfWeek::sun ? ' text-danger' : '' }}{{ $cell['date']->dayOfWeek == DayOfWeek::sat ? ' text-primary' : '' }}">
                                {{ $cell['date']->day . '(' . DayOfWeek::getDescription($cell['date']->dayOfWeek) . ')' }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        {{-- カレンダーデータ部の表示 --}}
                        @foreach ($calendar_details['calendar_cells'] as $cell)
                            <td class="
                                {{-- 日曜なら赤文字 --}}
                                {{ $cell['date']->dayOfWeek == DayOfWeek::sun ? 'text-danger' : '' }}
                                {{-- 土曜なら青文字 --}}
                                {{ $cell['date']->dayOfWeek == DayOfWeek::sat ? 'text-primary' : '' }}
                                {{-- 当日ならセル背景を黄色 --}}
                                {{ $cell['date'] == Carbon::today() ? ' current' : '' }}
                                "
                            >
                                <div class="clearfix">
                                    {{-- 日付＆曜日（767px以下で表示） --}}
                                    <div class="float-left d-md-none">
                                        {{ $cell['date']->day . ' (' . DayOfWeek::getDescription($cell['date']->dayOfWeek) . ')' }}
                                    </div>

                                    {{-- ＋ボタン --}}
                                    <div class="float-right">
                                        @auth
                                            {{-- セル毎に予約追加画面呼び出し用のformをセット --}}
                                            <form action="{{URL::to('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_edit_booking_{{ $reservations->id }}_{{ $calendar_details['facility']->id }}_{{ $cell['date']->format('Ymd') }}" method="POST" class="form-horizontal">
                                                {{ csrf_field() }}
                                                {{-- 施設予約ID --}}
                                                <input type="hidden" name="reservations_id" value="{{ $reservations->id }}">
                                                {{-- 施設ID --}}
                                                <input type="hidden" name="facility_id" value="{{ $calendar_details['facility']->id }}">
                                                {{-- 対象日付 --}}
                                                <input type="hidden" name="target_date" value="{{ $cell['date']->format('Ymd') }}">
                                                {{-- ＋ボタンクリックでformサブミット --}}
                                                <a href="javascript:form_edit_booking_{{ $reservations->id }}_{{ $calendar_details['facility']->id }}_{{ $cell['date']->format('Ymd') }}.submit()">
                                                    <i class="fas fa-plus-square fa-2x"></i>
                                                </a>
                                            </form>
                                        @endauth
                                    </div>
                                </div>
                                @if (isset($cell['bookings']))
                                    @foreach ($cell['bookings'] as $booking)
                                        <a href="#bookingDetailModal" role="button" data-toggle="modal" 
                                            {{-- モーダルウィンドウに渡す予約入力値をセット（固定項目） --}}
                                            data-booking_id="{{ $booking['booking_header']->id }}" 
                                            data-facility_name="{{ $facility_name }}" 
                                            data-reservation_date_display="{{ $booking['booking_header']->start_datetime->format('Y年n月j日') . ' (' . DayOfWeek::getDescription($booking['booking_header']->start_datetime->dayOfWeek) . ')' }}" 
                                            data-reservation_time="{{ substr($booking['booking_header']->start_datetime, 11, 5) . ' ~ ' . substr($booking['booking_header']->end_datetime, 11, 5) }}" 
                                            {{-- モーダルウィンドウに渡す予約入力値をセット（可変項目） --}}
                                            @foreach ($booking['booking_details'] as $bookingDetail)
                                                @switch($bookingDetail->column_type)
                                                    {{-- テキスト項目 --}}
                                                    @case(ReservationColumnType::text)

                                                        data-column_{{ $bookingDetail->column_id }}="{{ $bookingDetail->value ? $bookingDetail->value : " " }}"
                                                        @break

                                                    {{-- ラジオボタン項目 --}}
                                                    @case(ReservationColumnType::radio)

                                                        {{-- ラジオボタン項目の場合、valueにはreservations_columns_selectsテーブルのIDが入っているので、該当の選択肢データを取得して選択肢名をセットする --}}
                                                        @php
                                                            $filtered_select = $selects->first(function($select) use($bookingDetail) {
                                                                return $select->reservations_id == $bookingDetail->reservations_id && $select->column_id == $bookingDetail->id && $select->id == $bookingDetail->value;
                                                            });
                                                            $filtered_select->toArray();
                                                        @endphp
                                                            data-column_{{ $bookingDetail->column_id }}="{{ $filtered_select->select_name }}"
                                                            @break
                                                    @default
                                                        
                                                @endswitch
                                            @endforeach
                                        >
                                            {{-- 表示用の予約時間 --}}
                                            <div class="small">{{ substr($booking['booking_header']->start_datetime, 11, 5) . '~' . substr($booking['booking_header']->end_datetime, 11, 5) }}</div>
                                        </a>
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
    @endforeach
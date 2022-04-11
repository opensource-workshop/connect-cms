{{--
 * 施設予約データ表示画面（週）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

    {{-- 予約詳細モーダルウィンドウ --}}
    @include('plugins.user.reservations.default.include_calendar_modal')
    {{-- 施設詳細モーダルウィンドウ --}}
    @include('plugins.user.reservations.default.include_facility_modal')
    {{-- タブ表示 --}}
    @include('plugins.user.reservations.default.include_calendar_tab')

    {{-- defaultテンプレート --}}
    <div>

        {{-- メッセージエリア --}}
        @include('plugins.common.flash_message_for_frame')

        <div class="text-center mb-1">
            {{-- 前月ボタン. aタグを改行しない事でオンマウスのアンダーラインを表示しない --}}
            <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->subDay(7)->format('Ymd') }}#frame-{{$frame->id}}"><i class="fas fa-chevron-circle-left"></i></a>
            {{-- 当月表示 --}}
            @if (App::getLocale() == ConnectLocale::ja)
                <div class="h5 d-inline">{{$carbon_target_date->format('Y年')}}</div>
                <div class="h3 d-inline">{{$carbon_target_date->format('n月')}}</div>
            @else
                <div class="h3 d-inline">{{$carbon_target_date->format('M')}}</div>
                <div class="h5 d-inline">{{$carbon_target_date->format('Y')}}</div>
            @endif
            {{-- 翌月ボタン --}}
            <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->addDay(7)->format('Ymd') }}#frame-{{$frame->id}}"><i class="fas fa-chevron-circle-right"></i></a>

            {{-- 当日へボタン --}}
            <div class="d-inline align-bottom ml-3">
                <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ Carbon::today()->format('Ymd') }}#frame-{{$frame->id}}" class="badge badge-pill badge-info">
                    {{__('messages.to_today')}}
                </a>
            </div>
        </div>

        {{-- posts.createをループ外で判定 --}}
        @can('posts.create',[[null, $frame->plugin_name, $buckets]])
            @php $can_posts_create = true; @endphp
        @else
            @php $can_posts_create = false; @endphp
        @endcan

        {{-- 登録している施設分ループ --}}
        @foreach ($calendars as $facility_name => $calendar_details)

            {{-- 施設名 --}}
            @include('plugins.user.reservations.default.include_calendar_facility_name', ['action' => 'week'])

            {{-- カレンダーデータ部 --}}
            <table class="table table-bordered cc_responsive_table" style="table-layout:fixed;">
                <thead>
                    {{-- カレンダーヘッダ部の曜日を表示 --}}
                    <tr class="text-nowrap">
                        @foreach ($calendar_details['calendar_cells'] as $cell)
                            {{-- 日曜なら赤文字、土曜なら青文字 --}}
                            <th class="text-center bg-light{{ $cell['date']->dayOfWeek == DayOfWeek::sun ? ' cc-color-sunday' : '' }}{{ $cell['date']->dayOfWeek == DayOfWeek::sat ? ' cc-color-saturday' : '' }}">
                                @include('plugins.user.reservations.default.include_calendar_day', ['date' => $cell['date']])
                                @include('plugins.user.reservations.default.include_calendar_day_of_week', ['date' => $cell['date']])
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        {{-- カレンダーデータ部の表示 --}}
                        @foreach ($calendar_details['calendar_cells'] as $cell)
                            <td>
                                <div class="clearfix">
                                    {{-- 日付＆曜日（767px以下で表示） --}}
                                    <div class="float-left d-md-none font-weight-bold text-secondary">
                                        @include('plugins.user.reservations.default.include_calendar_day', ['date' => $cell['date']])
                                        @include('plugins.user.reservations.default.include_calendar_day_of_week', ['date' => $cell['date']])
                                        {{-- 祝日 --}}
                                        @if ($cell['date']->hasHoliday())
                                            <div class="pl-1 d-inline cc-font-90">
                                                <span class="badge badge-pill badge-danger">{{$cell['date']->getHolidayName()}}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- ＋ボタン --}}
                                    <div class="float-right">
                                        @if ($can_posts_create)
                                            {{-- 予約制限なしなら、＋ボタン表示 --}}
                                            @if (!$calendar_details['facility']->is_limited)
                                                {{-- セル毎に予約追加画面呼び出し用のformをセット --}}
                                                <form action="{{URL::to('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_edit_booking_{{$frame_id}}_{{ $reservations->id }}_{{ $calendar_details['facility']->id }}_{{ $cell['date']->format('Ymd') }}" method="POST" class="form-horizontal">
                                                    {{ csrf_field() }}
                                                    {{-- 施設予約ID --}}
                                                    {{-- <input type="hidden" name="reservations_id" value="{{ $reservations->id }}"> --}}
                                                    {{-- 施設ID --}}
                                                    <input type="hidden" name="facility_id" value="{{ $calendar_details['facility']->id }}">
                                                    {{-- 対象日付 --}}
                                                    <input type="hidden" name="target_date" value="{{ $cell['date']->format('Ymd') }}">
                                                    {{-- ＋ボタンクリックでformサブミット --}}
                                                    <a href="javascript:form_edit_booking_{{$frame_id}}_{{ $reservations->id }}_{{ $calendar_details['facility']->id }}_{{ $cell['date']->format('Ymd') }}.submit()">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                {{-- 祝日 --}}
                                @if ($cell['date']->hasHoliday())
                                    <div class="row pb-1 d-none d-md-block">
                                        <div class="col-12 cc-font-90">
                                            <span class="badge badge-pill badge-danger">{{$cell['date']->getHolidayName()}}</span>
                                        </div>
                                    </div>
                                @endif

                                @if (isset($cell['bookings']))
                                    @foreach ($cell['bookings'] as $booking)
                                        <div class="row py-1">
                                            <div class="d-md-none col-1"></div>
                                            <div class="col-11 col-md-12">

                                                {{-- 予約時間の表示 ＆ モーダルウィンドウ呼び出し --}}
                                                @include('plugins.user.reservations.default.include_calendar_modal_call')

                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        @endforeach

    </div>

@endsection



{{--
 * 施設予約データ表示画面（月）
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

    {{-- タブ表示 --}}
    @include('plugins.user.reservations.default.include_calendar_tab')

    {{-- defaultテンプレート --}}
    <div>

        {{-- メッセージエリア --}}
        @include('plugins.common.flash_message')

        <div class="text-center mb-1">
            {{-- 前月ボタン. aタグを改行しない事でオンマウスのアンダーラインを表示しない --}}
            <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->subMonthsNoOverflow('1')->format('Ym') }}#frame-{{$frame->id}}"><i class="fas fa-chevron-circle-left"></i></a>
            {{-- 当月表示 --}}
            @if (App::getLocale() == ConnectLocale::ja)
                <div class="h5 d-inline">{{$carbon_target_date->format('Y年')}}</div>
                <div class="h3 d-inline">{{$carbon_target_date->format('n月')}}</div>
            @else
                <div class="h3 d-inline">{{$carbon_target_date->format('M')}}</div>
                <div class="h5 d-inline">{{$carbon_target_date->format('Y')}}</div>
            @endif
            {{-- 翌月ボタン --}}
            <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->addMonthsNoOverflow('1')->format('Ym') }}#frame-{{$frame->id}}"><i class="fas fa-chevron-circle-right"></i></a>

            {{-- 今月へボタン --}}
            <div class="d-inline align-bottom ml-3">
                <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ Carbon::today()->format('Ym') }}#frame-{{$frame->id}}" class="badge badge-pill badge-info">
                    {{__('messages.to_this_month')}}
                </a>
            </div>
        </div>

        {{-- 登録している施設分ループ --}}
        @foreach ($calendars as $facility_name => $calendar_details)

            {{-- 施設名 --}}
            <span class="h5">＜{{ $facility_name }}＞</span>

            {{-- カレンダーデータ部 --}}
            <table class="table table-bordered cc_responsive_table" style="table-layout:fixed;">
                <thead>
                    {{-- カレンダーヘッダ部の曜日を表示 --}}
                    <tr>
                        @foreach (DayOfWeek::getMembers() as $key => $desc)
                            {{-- 日曜なら赤文字、土曜なら青文字 --}}
                            <th class="text-center bg-light{{ $key == DayOfWeek::sun ? ' text-danger' : '' }}{{ $key == DayOfWeek::sat ? ' text-primary' : '' }}">{{ $desc }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- カレンダーデータ部の表示 --}}
                    @foreach ($calendar_details['calendar_cells'] as $cell)
                        {{-- 日曜日なら新しい行 --}}
                        @if ($cell['date']->dayOfWeek == 0)
                            <tr>
                        @endif
                                <td class="
                                    {{-- 当月以外ならセル背景をグレーアウト --}}
                                    {{ $cell['date']->month != $carbon_target_date->month ? 'd-none d-md-table-cell bg-light' : '' }}
                                    {{-- 当月、且つ、日曜なら赤文字 --}}
                                    {{ $cell['date']->month == $carbon_target_date->month && $cell['date']->dayOfWeek == DayOfWeek::sun ? 'cc-color-sunday' : '' }}
                                    {{-- 当月、且つ、日曜なら赤文字 --}}
                                    {{ $cell['date']->month == $carbon_target_date->month && $cell['date']->dayOfWeek == DayOfWeek::sat ? 'cc-color-saturday' : '' }}
                                    "
                                >
                                    <div class="clearfix">
                                        {{-- 日付 --}}
                                        <div class="float-left font-weight-bold text-secondary">
                                            @include('plugins.user.reservations.default.include_calendar_day', ['date' => $cell['date']])

                                            {{-- 曜日（767px以下で表示） --}}
                                            <span class="d-md-none">
                                                @include('plugins.user.reservations.default.include_calendar_day_of_week', ['date' => $cell['date']])
                                            </span>
                                        </div>
                                        {{-- ＋ボタン --}}
                                        <div class="float-right">
                                            @can('posts.create',[[null, $frame->plugin_name, $buckets]])
                                                <a href="{{URL::to('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}?facility_id={{$calendar_details['facility']->id}}&target_date={{$cell['date']->format('Y-m-d')}}#frame-{{$frame_id}}">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
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
                        {{-- 土曜日なら行を閉じる --}}
                        @if ($cell['date']->dayOfWeek == 6)
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endforeach

    </div>

@endsection



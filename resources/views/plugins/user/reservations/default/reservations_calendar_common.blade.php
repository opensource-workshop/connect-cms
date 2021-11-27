{{--
 * 施設予約データ表示画面（月と週のラッパーテンプレート）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

    {{-- 予約詳細モーダルウィンドウ --}}
    @include('plugins.user.reservations.default.include_common_modal')

    {{-- タブ表示 --}}
    <ul class="nav nav-tabs justify-content-end mb-2">
        <li class="nav-item">
            {{-- 月タブ --}}
            <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ Carbon::now()->format('Ym') }}#frame-{{$frame->id}}"
                class="nav-link pt-0 pb-0 {{ $view_format == ReservationCalendarDisplayType::month ? ' active' : '' }}"
            >
                {{ __('messages.month') }}
            </a>
        </li>
        <li class="nav-item">
            {{-- 週タブ --}}
            <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ Carbon::today()->format('Ymd') }}#frame-{{$frame->id}}"
                class="nav-link pt-0 pb-0 {{ $view_format == ReservationCalendarDisplayType::week ? ' active' : '' }}"
            >
                {{ __('messages.week') }}
            </a>
        </li>
    </ul>

    {{-- defaultテンプレート --}}
    <div>

        {{-- メッセージエリア --}}
        @include('plugins.common.flash_message')

        @if ($view_format == ReservationCalendarDisplayType::month)

            {{-- 月で表示 --}}
            @include('plugins.user.reservations.default.reservations_calendar_month')

        @elseif ($view_format == ReservationCalendarDisplayType::week)

            {{-- 週で表示 --}}
            @include('plugins.user.reservations.default.reservations_calendar_week')

        @endif
    </div>

@endsection

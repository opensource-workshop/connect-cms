{{--
 * カレンダーのタブ表示
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 *
 * @param $is_template_designbase bool designbaseテンプレートか
--}}

@if (isset($is_template_designbase))
{{-- designbaseテンプレート --}}
<ul class="nav nav-tabs">
@else
{{-- defaultテンプレート --}}
<ul class="nav nav-tabs justify-content-end mb-2">
@endif

    <li class="nav-item">
        {{-- 月タブ --}}
        <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ Carbon::now()->format('Ym') }}#frame-{{$frame->id}}"
            @if (isset($is_template_designbase))
                {{-- designbaseテンプレート --}}
                class="nav-link{{ $view_format == ReservationCalendarDisplayType::month ? ' active' : '' }}"
            @else
                {{-- defaultテンプレート --}}
                class="nav-link pt-0 pb-0 {{ $view_format == ReservationCalendarDisplayType::month ? ' active' : '' }}"
            @endif
        >
            {{ __('messages.month') }}
        </a>
    </li>
    <li class="nav-item">
        {{-- 週タブ --}}
        <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ Carbon::today()->format('Ymd') }}#frame-{{$frame->id}}"
            @if (isset($is_template_designbase))
                {{-- designbaseテンプレート --}}
                class="nav-link{{ $view_format == ReservationCalendarDisplayType::week ? ' active' : '' }}"
            @else
                {{-- defaultテンプレート --}}
                class="nav-link pt-0 pb-0 {{ $view_format == ReservationCalendarDisplayType::week ? ' active' : '' }}"
            @endif
        >
            {{ __('messages.week') }}
        </a>
    </li>
</ul>

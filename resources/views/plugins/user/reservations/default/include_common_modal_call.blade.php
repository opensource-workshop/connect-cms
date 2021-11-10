{{--
 * 予約詳細モーダル呼び出し
--}}
@if ($booking['booking_header']->status == StatusType::approval_pending)
    @can('role_update_or_approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]])
        {{-- 承認待ち：リンクあり --}}

        <a href="#bookingDetailModal{{$frame_id}}" role="button" data-toggle="modal"
            {{-- モーダルウィンドウに渡す予約入力値をセット（固定項目） --}}
            data-booking_id="{{ $booking['booking_header']->id }}"
            data-facility_name="{{ $facility_name }}"
            data-reservation_date_display="{{$booking['booking_header']->displayDate()}}"
            data-reservation_time="{{ $booking['booking_header']->start_datetime->format('H:i')}} ~ {{$booking['booking_header']->end_datetime->format('H:i') }}"
            @can('posts.update', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_edit="1" @endcan
            @can('posts.delete', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_delete="1" @endcan
            @can('role_update_or_approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]])
                @if ($booking['booking_header']->status == StatusType::approval_pending) data-is_approval_pending="1" @endif
            @endcan
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
                            $filtered_select ? $filtered_select->toArray() : null;
                        @endphp
                            data-column_{{ $bookingDetail->column_id }}="{{ $filtered_select ? $filtered_select->select_name : '' }}"
                            @break
                    @default

                @endswitch
            @endforeach
        >
            {{-- 表示用の予約時間 --}}
            <div class="small">
                {{ $booking['booking_header']->start_datetime->format('H:i')}}~{{$booking['booking_header']->end_datetime->format('H:i') }}
                <span class="badge badge-warning align-bottom">承認待ち</span>
            </div>
        </a>

    @else
        {{-- 承認待ち：リンクなし --}}

        <div class="small">
            {{ $booking['booking_header']->start_datetime->format('H:i')}}~{{$booking['booking_header']->end_datetime->format('H:i') }}
            <span class="badge badge-warning align-bottom">承認待ち</span>
        </div>
    @endcan

@else
    {{-- 公開：リンクあり --}}

    <a href="#bookingDetailModal{{$frame_id}}" role="button" data-toggle="modal"
        {{-- モーダルウィンドウに渡す予約入力値をセット（固定項目） --}}
        data-booking_id="{{ $booking['booking_header']->id }}"
        data-facility_name="{{ $facility_name }}"
        data-reservation_date_display="{{$booking['booking_header']->displayDate()}}"
        data-reservation_time="{{ $booking['booking_header']->start_datetime->format('H:i')}} ~ {{$booking['booking_header']->end_datetime->format('H:i') }}"
        @can('posts.update', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_edit="1" @endcan
        @can('posts.delete', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_delete="1" @endcan
        @can('role_update_or_approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]])
            @if ($booking['booking_header']->status == StatusType::approval_pending) data-is_approval_pending="1" @endif
        @endcan
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
                        $filtered_select ? $filtered_select->toArray() : null;
                    @endphp
                        data-column_{{ $bookingDetail->column_id }}="{{ $filtered_select ? $filtered_select->select_name : '' }}"
                        @break
                @default

            @endswitch
        @endforeach
    >
        {{-- 表示用の予約時間 --}}
        <div class="small">
            {{ $booking['booking_header']->start_datetime->format('H:i')}}~{{$booking['booking_header']->end_datetime->format('H:i') }}
        </div>
    </a>

@endif

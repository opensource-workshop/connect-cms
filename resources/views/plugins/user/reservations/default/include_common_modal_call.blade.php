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
            @foreach ($columns as $column)
                @php
                    $obj = $booking['booking_details']->where('column_id', $column->id)->first();

                    // 項目の型で処理を分ける。
                    if ($column->column_type == ReservationColumnType::radio) {
                        // ラジオ型
                        if ($obj) {
                            // ラジオボタン項目の場合、valueにはreservations_columns_selectsテーブルのIDが入っているので、該当の選択肢データを取得して選択肢名をセットする
                            $filtered_select = $selects->where('column_id', $column->id)->where('id', $obj->value)->first();
                            $value = $filtered_select ? $filtered_select->select_name : '';
                        } else {
                            $value = '';
                        }
                    } elseif ($column->column_type == ReservationColumnType::created) {
                        // 登録日型
                        $value = $booking['booking_header']->created_at;
                    } elseif ($column->column_type == ReservationColumnType::updated) {
                        // 更新日型
                        $value = $booking['booking_header']->updated_at;
                    } elseif ($column->column_type == ReservationColumnType::created_name) {
                        // 登録者型
                        $value = $booking['booking_header']->created_name;
                    } elseif ($column->column_type == ReservationColumnType::updated_name) {
                        // 更新者型
                        $value = $booking['booking_header']->updated_name;
                    }  else {
                        // その他の型
                        $value = $obj ? $obj->value : "";
                    }
                @endphp
                data-column_{{ $column->id }}="{{ $value }}"
            @endforeach
        >
            {{-- 表示用の予約時間 --}}
            <div class="small">
                {{ $booking['booking_header']->start_datetime->format('H:i')}}~{{$booking['booking_header']->end_datetime->format('H:i') . ' ' . $booking['booking_header']->title }}
                <span class="badge badge-warning align-bottom">承認待ち</span>
            </div>
        </a>

    @else
        {{-- 承認待ち：リンクなし＆タイトル表示しない --}}

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
        @foreach ($columns as $column)
            @php
                $obj = $booking['booking_details']->where('column_id', $column->id)->first();

                // 項目の型で処理を分ける。
                if ($column->column_type == ReservationColumnType::radio) {
                    // ラジオ型
                    if ($obj) {
                        // ラジオボタン項目の場合、valueにはreservations_columns_selectsテーブルのIDが入っているので、該当の選択肢データを取得して選択肢名をセットする
                        $filtered_select = $selects->where('column_id', $column->id)->where('id', $obj->value)->first();
                        $value = $filtered_select ? $filtered_select->select_name : '';
                    } else {
                        $value = '';
                    }
                } elseif ($column->column_type == ReservationColumnType::created) {
                    // 登録日型
                    $value = $booking['booking_header']->created_at;
                } elseif ($column->column_type == ReservationColumnType::updated) {
                    // 更新日型
                    $value = $booking['booking_header']->updated_at;
                } elseif ($column->column_type == ReservationColumnType::created_name) {
                    // 登録者型
                    $value = $booking['booking_header']->created_name;
                } elseif ($column->column_type == ReservationColumnType::updated_name) {
                    // 更新者型
                    $value = $booking['booking_header']->updated_name;
                }  else {
                    // その他の型
                    $value = $obj ? $obj->value : "";
                }
            @endphp
            data-column_{{ $column->id }}="{{ $value }}"
        @endforeach
    >
        {{-- 表示用の予約時間 --}}
        <div class="small">
            {{ $booking['booking_header']->start_datetime->format('H:i')}}~{{$booking['booking_header']->end_datetime->format('H:i') . ' ' . $booking['booking_header']->title }}
        </div>
    </a>

@endif

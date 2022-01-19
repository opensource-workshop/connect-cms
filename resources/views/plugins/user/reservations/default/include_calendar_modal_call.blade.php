{{--
 * 予約詳細モーダル呼び出し
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
@if ($booking['booking_header']->status == StatusType::approval_pending)
    @can('role_update_or_approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]])
        {{-- 承認待ち：リンクあり --}}

        <a href="#bookingDetailModal{{$frame_id}}" role="button" data-toggle="modal"
            {{-- モーダルウィンドウに渡す予約入力値をセット（固定項目） --}}
            data-booking_id="{{ $booking['booking_header']->id }}"
            @can('posts.update', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_edit="1" @endcan
            @can('posts.delete', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_delete="1" @endcan
            @if ($booking['booking_header']->status == StatusType::approval_pending)
                @can('role_update_or_approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_approval_pending="1" @endcan
                @can('posts.approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_approval="1" @endcan
            @endif
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
        @can('posts.update', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_edit="1" @endcan
        @can('posts.delete', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_delete="1" @endcan
        @if ($booking['booking_header']->status == StatusType::approval_pending)
            @can('role_update_or_approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_approval_pending="1" @endcan
            @can('posts.approval', [[$booking['booking_header'], $frame->plugin_name, $buckets]]) data-is_approval="1" @endcan
        @endif
    >
        {{-- 表示用の予約時間 --}}
        <div class="small">
            {{ $booking['booking_header']->start_datetime->format('H:i')}}~{{$booking['booking_header']->end_datetime->format('H:i') . ' ' . $booking['booking_header']->title }}
        </div>
    </a>

@endif

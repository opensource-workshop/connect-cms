{{--
 * 施設予約データ表示画面（月と週のラッパーテンプレート）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

    {{-- 必要なデータ揃っているか確認 --}}
    @if (
        // フレームに紐づいた施設予約親データが存在すること
        isset($frame) && $frame->bucket_id &&
        // 施設データが存在すること
        !$facilities->isEmpty() &&
        // 予約項目データが存在すること
        !$columns->isEmpty()
        )

        @if ($view_format == ReservationCalendarDisplayType::month)

            {{-- 月で表示 --}}
            @include('plugins.user.reservations.default.reservations_calendar_month')

        @elseif ($view_format == ReservationCalendarDisplayType::week)

            {{-- 週で表示 --}}
            @include('plugins.user.reservations.default.reservations_calendar_week')

        @endif

    @else
        {{-- フレームに紐づくコンテンツがない場合、データ登録を促すメッセージを表示 --}}
        <div class="card border-danger">
            <div class="card-body">
                {{-- フレームに紐づく親データがない場合 --}}
                @if (!(isset($frame) && $frame->bucket_id))
                    <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する施設予約を選択するか、作成してください。</p>
                @endif
                {{-- 施設データがない場合 --}}
                @if ($facilities->isEmpty())
                    <p class="text-center cc_margin_bottom_0">フレームの設定画面から、施設データを作成してください。</p>
                @endif
                {{-- 予約項目データがない場合 --}}
                @if ($columns->isEmpty())
                    <p class="text-center cc_margin_bottom_0">フレームの設定画面から、予約項目データを作成してください。</p>
                @endif
            </div>
        </div>
    @endif
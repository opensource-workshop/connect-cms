{{--
 * 施設予約データ表示画面（月と週のラッパーテンプレート）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
 @extends('core.cms_frame_base')

 @section("plugin_contents_$frame->id")
    {{-- 必要なデータ揃っているか確認 --}}
    @if (
        // フレームに紐づいた施設予約親データが存在すること
        isset($frame) && $frame->bucket_id &&
        // 施設データが存在すること
        !$facilities->isEmpty() &&
        // 予約項目データが存在すること
        !$columns->isEmpty() &&
        // 予約項目で選択肢が指定されていた場合に選択肢データが存在すること
        $isExistSelect
        )

        {{-- 予約詳細モーダルウィンドウ --}}
        <div class="modal" id="bookingDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    {{-- ヘッダー --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                    </div>

                    {{-- メインコンテンツ --}}
                    <div class="modal-body">
                        <form>
                            {{-- 利用日 --}}
                            <div class="form-group row">
                                <label for="reservation_date_display" class="col-3 col-form-label">{{ __('messages.day_of_use')}}</label>
                                <input type="text" class="col-9 form-control-plaintext" id="reservation_date_display" readonly>
                            </div>
                            {{-- 利用時間 --}}
                            <div class="form-group row">
                                <label for="reservation_time" class="col-3 col-form-label">{{ __('messages.time_of_use')}}</label>
                                <input type="text" class="col-9 form-control-plaintext" id="reservation_time" readonly>
                            </div>
                            {{-- 予約可変項目 --}}
                            @foreach ($columns as $column)
                            <div class="form-group row">
                                <label for="column_{{ $column->id }}" class="col-3 col-form-label">{{ $column->column_name }}</label>
                                <input type="text" class="col-9 form-control-plaintext" id="column_{{ $column->id }}" readonly>
                            </div>
                            @endforeach
                        </form>
                    </div>

                    {{-- フッター --}}
                    <div class="modal-footer" style="justify-content : left;">
                        {{-- 閉じるボタン --}}
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> {{ __('messages.close') }}
                        </button>
                        {{-- 予約編集ボタン（ログイン時のみ表示） --}}
                        @auth
                            <form action="{{URL::to('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_edit_booking" method="POST" class="form-horizontal">
                                {{ csrf_field() }}

                                {{-- 予約ID --}}
                                <input type="hidden" name="booking_id" value="">
                                {{-- ＋ボタンクリックでformサブミット --}}
                                <a href="javascript:form_edit_booking.submit()">
                                    <button type="button" class="btn btn-success">
                                        <i class="far fa-edit"></i> {{ __('messages.edit') }}
                                    </button>
                                </a>
                            </form>
                            <form action="{{URL::to('/')}}/plugin/reservations/destroyBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_destroy_booking" method="POST" class="form-horizontal">
                                {{ csrf_field() }}

                                {{-- 予約ID --}}
                                <input type="hidden" name="booking_id" value="">
                                {{-- ＋ボタンクリックでformサブミット --}}
                                <a href="javascript:form_destroy_booking.submit()">
                                    <button type="button" class="btn btn-danger" onclick="javascript:return confirm('予約を削除します。\nよろしいですか？')">
                                        <i class="fas fa-trash-alt"></i> {{ __('messages.delete') }}
                                    </button>
                                </a>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            // モーダル表示前イベント時処理
            $('#bookingDetailModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget)
                var modal = $(this)
                // モーダルタイトル
                modal.find('.modal-title').text('{{ __('messages.reservation_details') }}（' + button.data('facility_name') + '）')
                // 予約項目（固定）
                modal.find('[name=booking_id]').val(button.data('booking_id'))
                modal.find('#reservation_date_display').val(button.data('reservation_date_display'))
                modal.find('#reservation_time').val(button.data('reservation_time'))
                // 予約項目（可変）
                @foreach ($columns as $column)
                    modal.find('#column_{{ $column->id }}').val(button.data('column_{{ $column->id }}'))
                @endforeach
            })
        </script>

        {{-- タブ表示 --}}
        <ul class="nav nav-tabs">
            <li class="nav-item">
                {{-- 月タブ --}}
                <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ Carbon::now()->format('Ym') }}#frame-{{$frame->id}}" 
                    class="nav-link{{ $view_format == ReservationCalendarDisplayType::month ? ' active' : '' }}"
                >
                    {{ __('messages.month') }}
                </a>
            </li>
            <li class="nav-item">
                {{-- 週タブ --}}
                <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ Carbon::today()->format('Ymd') }}#frame-{{$frame->id}}" 
                    class="nav-link{{ $view_format == ReservationCalendarDisplayType::week ? ' active' : '' }}"
                >
                    {{ __('messages.week') }}
                </a>
            </li>
        </ul>

        @if (isset($is_template_designbase))
            {{-- designbaseテンプレート --}}
            <div class="orderCalendar">
                @if ($view_format == ReservationCalendarDisplayType::month)

                    {{-- 月で表示 --}}
                    @include('plugins.user.reservations.designbase.reservations_calendar_month')

                @elseif ($view_format == ReservationCalendarDisplayType::week)

                    {{-- 週で表示 --}}
                    @include('plugins.user.reservations.designbase.reservations_calendar_week')

                @endif
            </div>

        @else
            {{-- defaultテンプレート --}}
            <div>
                @if ($view_format == ReservationCalendarDisplayType::month)

                    {{-- 月で表示 --}}
                    @include('plugins.user.reservations.default.reservations_calendar_month')

                @elseif ($view_format == ReservationCalendarDisplayType::week)

                    {{-- 週で表示 --}}
                    @include('plugins.user.reservations.default.reservations_calendar_week')

                @endif
            </div>
        @endif

    @else
        {{-- 未ログイン時は何も表示しない --}}
        @if (Auth::check())
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
                    {{-- 予約項目で選択肢のデータ型が指定されていた時に選択肢データがない場合 --}}
                    @if (!$isExistSelect)
                        <p class="text-center cc_margin_bottom_0">フレームの設定画面から、予約項目の選択肢データを作成してください。</p>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
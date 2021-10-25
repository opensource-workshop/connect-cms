{{--
 * 予約詳細モーダルウィンドウ
--}}
<div class="modal" id="bookingDetailModal{{$frame_id}}" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel{{$frame_id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            {{-- ヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel{{$frame_id}}"></h5>
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
                    <form action="{{URL::to('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_edit_booking{{$frame_id}}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        {{-- 予約ID --}}
                        <input type="hidden" name="booking_id" value="">
                        {{-- ＋ボタンクリックでformサブミット --}}
                        <a href="javascript:form_edit_booking{{$frame_id}}.submit()">
                            <button type="button" class="btn btn-success">
                                <i class="far fa-edit"></i> {{ __('messages.edit') }}
                            </button>
                        </a>
                    </form>
                @endauth

                {{-- 詳細画面 --}}
                <form action="" name="form_show_booking{{$frame_id}}" method="get" class="form-horizontal">
                    {{-- input_id(予約ID) --}}
                    <input type="hidden" name="booking_id" value="">
                    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/reservations/showBooking/{{$page->id}}/{{$frame_id}}/' + form_show_booking{{$frame_id}}.booking_id.value + '#frame-{{$frame->id}}'">
                        {{ __('messages.detail') }}  <i class="fas fa-angle-right"></i>
                    </button>
                </form>

                @auth
                    <form action="{{URL::to('/')}}/plugin/reservations/destroyBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_destroy_booking{{$frame_id}}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        {{-- 予約ID --}}
                        <input type="hidden" name="booking_id" value="">
                        {{-- ＋ボタンクリックでformサブミット --}}
                        <a href="javascript:form_destroy_booking{{$frame_id}}.submit()">
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
    $('#bookingDetailModal{{$frame_id}}').on('show.bs.modal', function (event) {
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

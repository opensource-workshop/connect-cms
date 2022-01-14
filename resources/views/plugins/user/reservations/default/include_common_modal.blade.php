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
                {{-- 利用日 --}}
                <div class="row">
                    <label for="reservation_date_display" class="col-3 col-form-label">{{ __('messages.day_of_use')}}</label>
                    <div class="col-9 form-control-plaintext" id="reservation_date_display"></div>
                </div>
                {{-- 利用時間 --}}
                <div class="row">
                    <label for="reservation_time" class="col-3 col-form-label">{{ __('messages.time_of_use')}}</label>
                    <div class="col-9 form-control-plaintext" id="reservation_time"></div>
                </div>

                {{-- 繰り返し --}}
                <div class="row" id="reservation_repeat_div">
                    <label class="col-3 col-form-label">{{__('messages.repetition')}}</label>
                    <div class="col-9 form-control-plaintext">
                        <span id="reservation_repeat"></span><br />
                        <span id="reservation_repeat_end"></span>
                    </div>
                </div>

                {{-- 予約可変項目エリア --}}
                <div id="bookingDetailModalColumns{{$frame_id}}"></div>
                {{-- 承認待ち --}}
                <div id="reservation_approval_pending_badge">
                    <span class="badge badge-warning align-bottom">承認待ち</span>
                </div>
            </div>

            {{-- フッター --}}
            <div class="modal-footer" style="justify-content : left;">
                <form action="" name="form_booking{{$frame_id}}" method="get">
                    {{-- input_id(予約ID) --}}
                    <input type="hidden" name="booking_id" value="">
                    <input type="hidden" name="inputs_parent_id" value="">
                </form>

                {{-- 閉じるボタン --}}
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> {{ __('messages.close') }}</span>
                </button>

                {{-- 予約編集ボタン --}}
                @auth
                    <button type="button" class="btn btn-success" id="reservation_edit_button" onclick="location.href='{{url('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}/' + form_booking{{$frame_id}}.booking_id.value + '#frame-{{$frame->id}}'">
                        <i class="far fa-edit"></i> {{ __('messages.edit') }}
                    </button>

                    {{-- 繰り返しパターン --}}
                    <div class="btn-group" id="reservation_repeat_edit_button">
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="far fa-edit"></i> {{ __('messages.edit') }}
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-item" type="button" onclick="location.href='{{url('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}/' + form_booking{{$frame_id}}.booking_id.value + '?edit_plan_type={{EditPlanType::only}}#frame-{{$frame->id}}'">
                                {{ __('messages.repeat_edit_plan_only', ['action' => __('messages.change')]) }}
                            </button>
                            <button class="dropdown-item" type="button" onclick="location.href='{{url('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}/' + form_booking{{$frame_id}}.booking_id.value + '?edit_plan_type={{EditPlanType::after}}#frame-{{$frame->id}}'">
                                {{ __('messages.repeat_edit_plan_after', ['action' => __('messages.change')]) }}
                            </button>
                            <button class="dropdown-item" type="button" onclick="location.href='{{url('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}/' + form_booking{{$frame_id}}.inputs_parent_id.value + '?edit_plan_type={{EditPlanType::all}}#frame-{{$frame->id}}'">
                                {{ __('messages.repeat_edit_plan_all', ['action' => __('messages.change')]) }}
                            </button>
                        </div>
                    </div>
                @endauth

                {{-- 詳細 --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/reservations/showBooking/{{$page->id}}/{{$frame_id}}/' + form_booking{{$frame_id}}.booking_id.value + '#frame-{{$frame->id}}'">
                    {{ __('messages.detail') }} <i class="fas fa-angle-right"></i>
                </button>

                @auth
                    <form action="" name="form_destroy_booking{{$frame_id}}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        {{-- 予約ID --}}
                        <input type="hidden" name="booking_id" value="">
                        <input type="hidden" name="inputs_parent_id" value="">
                        <button type="button" class="btn btn-danger" id="reservation_destroy_button" onclick="destroy_booking{{$frame_id}}()">
                            <i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> {{ __('messages.delete') }}</span>
                        </button>

                        {{-- 繰り返しパターン --}}
                        <div class="btn-group" id="reservation_repeat_destroy_button">
                            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> {{ __('messages.delete') }}</span>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item" type="button" onclick="destroy_repeat_booking{{$frame_id}}(form_destroy_booking{{$frame_id}}.booking_id.value, '{{EditPlanType::only}}')">
                                    {{ __('messages.repeat_edit_plan_only', ['action' => __('messages.delete')]) }}
                                </button>
                                <button class="dropdown-item" type="button" onclick="destroy_repeat_booking{{$frame_id}}(form_destroy_booking{{$frame_id}}.booking_id.value, '{{EditPlanType::after}}')">
                                    {{ __('messages.repeat_edit_plan_after', ['action' => __('messages.delete')]) }}
                                </button>
                                <button class="dropdown-item" type="button" onclick="destroy_repeat_booking{{$frame_id}}(form_destroy_booking{{$frame_id}}.inputs_parent_id.value, '{{EditPlanType::all}}')">
                                    {{ __('messages.repeat_edit_plan_all', ['action' => __('messages.delete')]) }}
                                </button>
                            </div>
                        </div>
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

        // Ajaxが終わるまで非表示
        modal.find('.modal-content').hide();

        $.ajax('{{url('/')}}/json/reservations/showBookingJson/{{$page->id}}/{{$frame_id}}/' + button.data('booking_id'),
            {
                type: 'get',
                dataType: 'json',
                // Ajax(XMLHttpRequest)の同期は非推奨のため使わない
                // async : false
            }
        )
        .done(function(data) {
            // console.log(data);

            // モーダルタイトル
            modal.find('.modal-title').text('{{ __('messages.reservation_details') }}（' + data.inputs.facility_name + '）');
            // 予約項目（固定）
            modal.find('[name=booking_id]').val(button.data('booking_id'));
            modal.find('[name=inputs_parent_id]').val(data.inputs.inputs_parent_id);
            modal.find('#reservation_date_display').text(data.inputs.reservation_date_display);
            modal.find('#reservation_time').text(data.inputs.reservation_time_display);

            // 繰り返しありで表示
            if (data.repeat.id) {
                modal.find('#reservation_repeat_div').show();
                modal.find('#reservation_repeat').text(data.repeat.reservation_repeat_display);
                modal.find('#reservation_repeat_end').text(data.repeat.reservation_repeat_end_display);

                // ボタン切替
                modal.find('#reservation_edit_button').hide();
                modal.find('#reservation_repeat_edit_button').show();

                modal.find('#reservation_destroy_button').hide();
                modal.find('#reservation_repeat_destroy_button').show();
            }

            // 予約項目（可変）
            // 予約項目（可変）エリアをクリア
            $('#bookingDetailModalColumns{{$frame_id}}').empty();

            for (var i in data.columns) {
                // {{-- <div class="row">
                //     <label for="column_{{ $column->id }}" class="col-3 col-form-label">{{ $column->column_name }}</label>
                //     <input type="text" class="col-9 form-control-plaintext" id="column_{{ $column->id }}" readonly>
                // </div> --}}

                if (data.columns[i].column_type == '{{ReservationColumnType::wysiwyg}}') {
                    // wysiwyg
                    $('#bookingDetailModalColumns{{$frame_id}}').append(
                        $('<div></div>')
                        .attr({
                            class: 'row'
                        })
                        .append(
                            $('<label></label>')
                            .attr({
                                for: 'column_' + data.columns[i].id,
                                class: 'col-3 col-form-label'
                            })
                            .text(data.columns[i].column_name)
                        )
                        .append(
                            $('<div></div>')
                            .attr({
                                class: 'col-9 form-control-plaintext',
                                id: 'column_' + data.columns[i].id
                            })
                            // HTMLで出力
                            .html(data.columns[i].display_value)
                        )
                    );
                } else {
                    // 通常
                    $('#bookingDetailModalColumns{{$frame_id}}').append(
                        $('<div></div>')
                        .attr({
                            class: 'row'
                        })
                        .append(
                            $('<label></label>')
                            .attr({
                                for: 'column_' + data.columns[i].id,
                                class: 'col-3 col-form-label'
                            })
                            .text(data.columns[i].column_name)
                        )
                        .append(
                            $('<div></div>')
                            .attr({
                                class: 'col-9 form-control-plaintext',
                                id: 'column_' + data.columns[i].id
                            })
                            // テキストで出力
                            .text(data.columns[i].display_value)
                        )
                    );
                }

            }

            // Ajax終了時に表示
            modal.find('.modal-content').show();
        })
        .fail(function() {
            window.alert('予約の詳細データが取得できませんでした。');

            // Ajax終了時に表示
            modal.find('.modal-content').show();
        });

        // {{-- モーダルタイトル
        // modal.find('.modal-title').text('{{ __('messages.reservation_details') }}（' + button.data('facility_name') + '）')
        // 予約項目（固定）
        // modal.find('[name=booking_id]').val(button.data('booking_id'))
        // modal.find('#reservation_date_display').val(button.data('reservation_date_display'))
        // modal.find('#reservation_time').val(button.data('reservation_time'))
        // 予約項目（可変）
        // @foreach ($columns as $column)
        //     modal.find('#column_{{ $column->id }}').val(button.data('column_{{ $column->id }}'))
        // @endforeach --}}

        // 繰り返しは初期、非表示
        modal.find('#reservation_repeat_div').hide();

        @auth
            // 編集権限ありならボタン表示, なしは非表示
            if (button.data('is_edit') == '1') {
                // find結果はjquery object
                modal.find('#reservation_edit_button').show();
                modal.find('#reservation_repeat_edit_button').hide();
            } else {
                modal.find('#reservation_edit_button').hide();
                modal.find('#reservation_repeat_edit_button').hide();
            }

            // 削除権限ありならボタン表示, なしは非表示
            if (button.data('is_delete') == '1') {
                // find結果はjquery object
                modal.find('#reservation_destroy_button').show();
                modal.find('#reservation_repeat_destroy_button').hide();
            } else {
                modal.find('#reservation_destroy_button').hide();
                modal.find('#reservation_repeat_destroy_button').hide();
            }
        @endauth

        // 承認待ちがありなら表示, なしは非表示
        if (button.data('is_approval_pending') == '1') {
            // finc結果はjquery object
            modal.find('#reservation_approval_pending_badge').show();
        } else {
            modal.find('#reservation_approval_pending_badge').hide();
        }
    })

    function destroy_booking{{$frame_id}}() {
        if (confirm('予約を削除します。\nよろしいですか？')) {
            form_destroy_booking{{$frame_id}}.action = "{{url('/')}}/redirect/plugin/reservations/destroyBooking/{{$page->id}}/{{$frame_id}}/" + form_destroy_booking{{$frame_id}}.booking_id.value + "#frame-{{$frame->id}}";
            form_destroy_booking{{$frame_id}}.submit();
        }
    }

    function destroy_repeat_booking{{$frame_id}}(booking_id, edit_plan_type) {
        if (confirm('予約を削除します。\nよろしいですか？')) {
            form_destroy_booking{{$frame_id}}.action = "{{url('/')}}/redirect/plugin/reservations/destroyBooking/{{$page->id}}/{{$frame_id}}/" + booking_id + "?edit_plan_type=" + edit_plan_type + "#frame-{{$frame->id}}";
            form_destroy_booking{{$frame_id}}.submit();
        }
    }
</script>

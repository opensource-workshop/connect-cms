{{--
 * 施設詳細モーダルウィンドウ
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
<div class="modal" id="facilityDetailModal{{$frame_id}}" tabindex="-1" role="dialog" aria-labelledby="facilityModalLabel{{$frame_id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            {{-- ヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title" id="facilityModalLabel{{$frame_id}}"></h5>
            </div>

            {{-- メインコンテンツ --}}
            <div class="modal-body">
                {{-- 利用時間 --}}
                <div class="form-row">
                    <label for="reservation_time" class="col-3 col-form-label">{{ __('messages.time_of_use')}}</label>
                    <div class="col-9 form-control-plaintext" id="reservation_time"></div>
                </div>

                {{-- 施設カテゴリ --}}
                <div class="form-row">
                    <label for="reservations_category" class="col-3 col-form-label">{{ __('messages.reservations_category')}}</label>
                    <div class="col-9 form-control-plaintext" id="reservations_category"></div>
                </div>

                {{-- 施設管理者 --}}
                <div class="form-row" id="facility_manager_name_row">
                    <label for="facility_manager_name" class="col-3 col-form-label">{{ __('messages.facility_manager_name')}}</label>
                    <div class="col-9 form-control-plaintext" id="facility_manager_name"></div>
                </div>

                {{-- 重複予約 --}}
                <div class="form-row" id="duplicate_booking_row">
                    <label for="duplicate_booking" class="col-3 col-form-label">{{ __('messages.duplicate_booking')}}</label>
                    <div class="col-9 form-control-plaintext" id="duplicate_booking">{{ __('messages.possible')}}</div>
                </div>

                {{-- 予約制限 --}}
                <div class="form-row" id="booking_restrictions_row">
                    <label for="booking_restrictions" class="col-3 col-form-label">{{ __('messages.booking_restrictions')}}</label>
                    <div class="col-9 form-control-plaintext" id="booking_restrictions">{{ __('messages.booking_restrictions_limited')}}</div>
                </div>

                {{-- 補足 --}}
                <div class="form-row">
                    <div class="col" id="supplement"></div>
                </div>
            </div>

            {{-- フッター --}}
            <div class="modal-footer" style="justify-content : left;">
                {{-- 閉じるボタン --}}
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> {{ __('messages.close') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // モーダル表示前イベント時処理
    $('#facilityDetailModal{{$frame_id}}').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        var modal = $(this)

        // Ajaxが終わるまで非表示
        modal.find('.modal-content').hide();

        $.ajax('{{url('/')}}/json/reservations/showFacilityJson/{{$page->id}}/{{$frame_id}}/' + button.data('facility_id'),
            {
                type: 'get',
                dataType: 'json',
                // Ajax(XMLHttpRequest)の同期は非推奨のため使わない
                // async : false
            }
        )
        .done(function(data) {
            // モーダルタイトル
            modal.find('.modal-title').text('{{ __('messages.facility_details') }}（' + data.facility.facility_name + '）');
            // 予約項目（固定）
            modal.find('#reservation_time').text(data.facility.reservation_time_display);
            modal.find('#reservations_category').text(data.facility.category);
            modal.find('#supplement').html(data.facility.supplement);

            // 施設管理者ありで表示
            if (data.facility.facility_manager_name) {
                modal.find('#facility_manager_name_row').show();
                modal.find('#facility_manager_name').text(data.facility.facility_manager_name);
            } else {
                modal.find('#facility_manager_name_row').hide();
            }

            // 重複予約OKで表示
            if (data.facility.is_allow_duplicate) {
                modal.find('#duplicate_booking_row').show();
            } else {
                modal.find('#duplicate_booking_row').hide();
            }

            // 権限で予約制限ありで表示
            if (data.facility.is_limited_by_role) {
                modal.find('#booking_restrictions_row').show();
            } else {
                modal.find('#booking_restrictions_row').hide();
            }

            // Ajax終了時に表示
            modal.find('.modal-content').show();
        })
        .fail(function() {
            window.alert('施設の詳細データが取得できませんでした。');

            // Ajax終了時に表示
            modal.find('.modal-content').show();
        });
    })
</script>

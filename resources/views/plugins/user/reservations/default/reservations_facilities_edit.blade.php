{{--
 * 施設の編集画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
 @extends('core.cms_frame_base_setting')

 @section("core.cms_frame_edit_tab_$frame->id")
     {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.reservations.reservations_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@auth
@if (!$reservation->id)
    <div class="alert alert-warning mt-2">
        <i class="fas fa-exclamation-circle"></i>
        使用する施設予約を選択するか、新規作成してください。
    </div>
@else

<script type="text/javascript">
    {{-- 施設追加のsubmit JavaScript --}}
    function submit_add_facility(btn) {
        form_facilities.action = "/plugin/reservations/addFacility/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        btn.disabled = true;
        form_facilities.submit();
    }

    {{-- 施設更新のsubmit JavaScript --}}
    function submit_update_facility(facility_id) {
        form_facilities.action = "/plugin/reservations/updateFacility/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_facilities.facility_id.value = facility_id;
        form_facilities.submit();
    }

    {{-- 表示順操作のsubmit JavaScript --}}
    function submit_display_sequence(facility_id, display_sequence, display_sequence_operation) {
        form_facilities.action = "/plugin/reservations/updateFacilitySequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_facilities.facility_id.value = facility_id;
        form_facilities.display_sequence.value = display_sequence;
        form_facilities.display_sequence_operation.value = display_sequence_operation;
        form_facilities.submit();
    }
    // ツールチップ
    $(function () {
        // 有効化
        $('[data-toggle="tooltip"]').tooltip()
    })

</script>

{{-- キャンセル用のフォーム。キャンセル時はセッションをクリアするため、トークン付きでPOST でsubmit したい。 --}}
<form action="/redirect/plugin/reservations/cancel/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="reservations_cancel" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
    {{ csrf_field() }}
</form>

<!-- Add or Update Form Button -->
<div class="form-group">
    <form action="/plugin/reservations/addFacility/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_facilities" name="form_facilities" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="reservations_id" value="{{$reservation->id}}">
        <input type="hidden" name="facility_id" value="">
        <input type="hidden" name="display_sequence" value="">
        <input type="hidden" name="display_sequence_operation" value="">
        <input type="hidden" name="return_frame_action" value="edit">
        {{-- メッセージエリア --}}
        <div class="alert alert-info mt-2">
            <i class="fas fa-exclamation-circle"></i> {{ $message ? $message : '予約対象の施設を追加・変更します。' }}
        </div>

        <div class="table-responsive">

            {{-- 施設の一覧 --}}
            <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    @if (count($facilities) > 0)
                        <th nowrap>表示順の操作</th>
                        <th nowrap>施設名</th>
                        <th nowrap>非表示 <span class="fas fa-info-circle" data-toggle="tooltip" title="チェックした施設を施設予約カレンダーから非表示にします。"></th>
                        <th nowrap>更新</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                {{-- 更新用の行 --}}
                @foreach($facilities as $facility)
                    @include('plugins.user.reservations.default.reservations_facilities_edit_row')
                @endforeach
                {{-- 新規登録用の行 --}}
                <tr class="thead-light">
                    <th colspan="4">【施設の追加行】</th>
                </tr>
                @include('plugins.user.reservations.default.reservations_facilities_edit_row_add')
                </tr>
            </tbody>
            </table>
        </div>
        {{-- エラーメッセージエリア --}}
        @if ($errors && $errors->any())
            <div class="alert alert-danger mt-2">
                @foreach ($errors->all() as $error)
                <i class="fas fa-exclamation-circle"></i>
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif
        {{-- ボタンエリア --}}
        <div class="text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="javascript:reservations_cancel.submit();"><i class="fas fa-times"></i> キャンセル</button>
        </div>
    </form>
</div>
@endif
@endauth
@endsection

{{--
 * 予約項目の編集画面
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
    {{-- 予約項目の追加のsubmit JavaScript --}}
    function submit_add_column(btn) {
        form_columns.action = "/plugin/reservations/addColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        btn.disabled = true;
        form_columns.submit();
    }

    {{-- 予約項目の更新のsubmit JavaScript --}}
    function submit_update_column(column_id) {
        form_columns.action = "/plugin/reservations/updateColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_columns.column_id.value = column_id;
        form_columns.submit();
    }

    {{-- 予約項目の表示順操作のsubmit JavaScript --}}
    function submit_display_sequence(column_id, display_sequence, display_sequence_operation) {
        form_columns.action = "/plugin/reservations/updateColumnSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_columns.column_id.value = column_id;
        form_columns.display_sequence.value = display_sequence;
        form_columns.display_sequence_operation.value = display_sequence_operation;
        form_columns.submit();
    }

    // ツールチップ
    $(function () {
        // 有効化
        $('[data-toggle="tooltip"]').tooltip()
        // 常時表示 ※表示の判定は項目側で実施
        $('#select-button-tip').tooltip('show');
    })
</script>

{{-- キャンセル用のフォーム。キャンセル時はセッションをクリアするため、トークン付きでPOST でsubmit したい。 --}}
<form action="/redirect/plugin/reservations/cancel/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="reservations_cancel" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
    {{ csrf_field() }}
</form>

<div class="form-group">
    <form action="/plugin/reservations/addColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_columns" name="form_columns" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="reservations_id" value="{{$reservation->id}}">
        <input type="hidden" name="column_id" value="">
        <input type="hidden" name="display_sequence" value="">
        <input type="hidden" name="display_sequence_operation" value="">
        <input type="hidden" name="return_frame_action" value="edit">
        {{-- メッセージエリア --}}
        <div class="alert alert-info mt-2">
            <i class="fas fa-exclamation-circle"></i> {{ $message ? $message : '予約登録時の項目を追加・変更します。' }}
        </div>

        <div class="table-responsive">

            {{-- 予約項目の一覧 --}}
            <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    @if (count($columns) > 0)
                        <th nowrap>表示順の操作</th>
                        <th nowrap>項目名</th>
                        <th nowrap>型</th>
                        <th nowrap>必須 <span class="fas fa-info-circle" data-toggle="tooltip" title="必須項目として指定します。"></th>
                        <th nowrap>非表示 <span class="fas fa-info-circle" data-toggle="tooltip" title="チェックした項目を非表示にします。"></th>
                        <th nowrap>選択肢の設定</th>
                        <th nowrap>更新</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                {{-- 更新用の行 --}}
                @foreach($columns as $column)
                    @include('plugins.user.reservations.default.reservations_columns_edit_row')
                @endforeach
                {{-- 新規登録用の行 --}}
                <tr class="thead-light">
                    <th colspan="7">【予約項目の追加行】</th>
                </tr>
                @include('plugins.user.reservations.default.reservations_columns_edit_row_add')
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

{{--
 * 施設予約の予約項目の選択肢設定画面
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
<script type="text/javascript">
    /**
     * 選択肢追加ボタン押下
     */
    function submit_add_select(btn) {
        form_selects.action = "{{url('/')}}/plugin/reservations/addSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        btn.disabled = true;
        form_selects.submit();
    }

    /**
     * 表示順操作ボタン押下
     */
    function submit_display_sequence(select_id, display_sequence, display_sequence_operation) {
        form_selects.action = "{{url('/')}}/plugin/reservations/updateSelectSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_selects.select_id.value = select_id;
        form_selects.display_sequence.value = display_sequence;
        form_selects.display_sequence_operation.value = display_sequence_operation;
        form_selects.submit();
    }

    /**
     * 選択肢の更新ボタン押下
     */
    function submit_update_select(select_id) {
        form_selects.action = "{{url('/')}}/plugin/reservations/updateSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_selects.select_id.value = select_id;
        form_selects.submit();
    }

    // ツールチップ
    $(function () {
        // 有効化
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

<form action="" id="form_selects" name="form_selects" method="POST" class="form-horizontal">

    {{ csrf_field() }}
    <input type="hidden" name="reservations_id" value="{{ $reservation->id }}">
    <input type="hidden" name="column_id" value="{{ $column->id }}">
    <input type="hidden" name="select_id" value="">
    <input type="hidden" name="display_sequence" value="">
    <input type="hidden" name="display_sequence_operation" value="">

    {{-- メッセージエリア --}}
    <div class="alert alert-info mt-2">
        <i class="fas fa-exclamation-circle"></i> {{ $message ? $message : '予約項目【' . $column->column_name . ' 】の選択肢を追加・変更します。' }}
    </div>


    <div class="table-responsive">

        {{-- 選択項目の一覧 --}}
        <table class="table table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    @if (count($selects) > 0)
                        <th class="text-center" nowrap>表示順</th>
                        <th class="text-center" nowrap>選択肢名</th>
                        <th class="text-center" nowrap>非表示 <span class="fas fa-info-circle" data-toggle="tooltip" title="チェックした選択肢を非表示にします。"></th>
                        <th class="text-center" nowrap>更新</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                {{-- 更新用の行 --}}
                @foreach($selects as $select)
                    <tr  @if (isset($select->hide_flag)) class="table-secondary" @endif>
                        {{-- 表示順操作 --}}
                        <td class="text-center" nowrap>
                            {{-- 上移動 --}}
                            <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'up')">
                                <i class="fas fa-arrow-up"></i>
                            </button>

                            {{-- 下移動 --}}
                            <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'down')">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </td>

                        {{-- 選択肢名 --}}
                        <td>
                            <input class="form-control" type="text" name="select_name_{{ $select->id }}" value="{{ old('select_name_'.$select->id, $select->select_name)}}">
                        </td>

                        {{-- 非表示フラグ --}}
                        <td class="align-middle text-center">
                            <input name="hide_flag_{{ $select->id }}" id="hide_flag_{{ $select->id }}" value="1" type="checkbox" @if (isset($select->hide_flag)) checked="checked" @endif>
                        </td>

                        {{-- 更新ボタン --}}
                        <td class="align-middle text-center">
                            <button
                                class="btn btn-primary cc-font-90 text-nowrap"
                                onclick="javascript:submit_update_select({{ $select->id }});"
                            >
                                <i class="fas fa-check"></i> <span class="d-sm-none">更新</span>
                            </button>
                        </td>
                    </tr>
                @endforeach
                <tr class="thead-light">
                    <th colspan="7">【選択肢の追加行】</th>
                </tr>

                {{-- 新規登録用の行 --}}
                <tr>
                    <td></td>
                    <td>
                        {{-- 選択肢名 --}}
                        <input class="form-control" type="text" name="select_name" value="{{ old('select_name') }}" placeholder="選択肢名">
                    </td>
                    <td class="text-center">
                        {{-- ＋ボタン --}}
                        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_select(this);"><i class="fas fa-plus"></i> <span class="d-sm-none">追加</span></button>
                    </td>
                    <td></td>
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
    <div class="form-group text-center">
        <a href="{{url('/')}}/plugin/reservations/editColumns/{{$page->id}}/{{$frame_id}}/#frame-{{$frame->id}}" class="btn btn-secondary">
            <i class="fas fa-chevron-left"></i> 項目設定へ
        </a>
    </div>
</form>
@endsection

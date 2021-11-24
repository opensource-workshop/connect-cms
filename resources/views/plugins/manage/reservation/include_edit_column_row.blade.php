{{--
 * 予約項目の更新行
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設管理
--}}
<tr @if ($column->hide_flag) class="table-secondary" @endif>
    {{-- 表示順操作 --}}
    <td class="align-middle text-center" nowrap>
        {{-- 上移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $column->id }}, {{ $column->display_sequence }}, 'up')">
            <i class="fas fa-arrow-up"></i>
        </button>

        {{-- 下移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $column->id }}, {{ $column->display_sequence }}, 'down')">
            <i class="fas fa-arrow-down"></i>
        </button>
    </td>
    {{-- 予約項目名 --}}
    <td>
        <input class="form-control @if ($errors && $errors->has('column_name_'.$column->id)) border-danger @endif" type="text" name="column_name_{{ $column->id }}" value="{{ old('column_name_'.$column->id, $column->column_name)}}">
    </td>
    {{-- 入力データ型 --}}
    <td>
        <select class="form-control" name="column_type_{{ $column->id }}" style="min-width: 140px;">
            <option value="" disabled>型を指定</option>
            @foreach (ReservationColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}" @if ($key == old("column_type_$column->id", $column->column_type)) selected="selected" @endif>{{ $value }}</option>
            @endforeach
        </select>
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required_{{ $column->id }}" value="1" @if ($column->required == Required::on) checked="checked" @endif>
    </td>
    {{-- 非表示フラグ --}}
    <td class="align-middle text-center">
        <input name="hide_flag_{{ $column->id }}" id="hide_flag_{{ $column->id }}" value="1" type="checkbox" @if ($column->hide_flag) checked="checked" @endif>
    </td>
    {{-- 選択肢の設定ボタン --}}
    <td class="text-center px-2">
        <button
            type="button"
            class="btn btn-success btn-xs cc-font-90 text-nowrap"
            @if ($column->column_type == ReservationColumnType::radio)
                {{-- 選択肢の設定がない場合のみツールチップを表示 --}}
                @if ($column->select_count == 0)
                    id="detail-button-tip" data-toggle="tooltip" title="選択肢がありません。設定してください。" data-trigger="manual" data-placement="bottom"
                @endif
            @endif
            onclick="location.href='{{url('/')}}/manage/reservation/editColumnDetail/{{ $column->id }}'"
        >
            <i class="far fa-window-restore"></i> <span class="d-sm-none">詳細</span>
        </button>
    </td>
    {{-- 更新ボタン --}}
    <td class="text-center px-2">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_update_column({{ $column->id }});">
            <i class="fas fa-check"></i> <span class="d-sm-none">更新</span>
        </button>
    </td>
</tr>
{{-- 選択肢の設定内容の表示行 --}}
@if (
    $column->column_type == ReservationColumnType::radio ||
    $column->title_flag
    )
    <tr>
        <td class="pt-0 border border-0"></td>
        <td class="pt-0 border border-0" colspan="7">
            @if ($column->select_count > 0)
                {{-- 選択肢データがある場合、カンマ付で一覧表示する --}}
                <i class="far fa-list-alt"></i>
                {{ $column->select_names }}
            @elseif (!$column->title_flag && $column->select_count == 0)
                {{-- 選択肢データがなく、タイトル指定もない場合はツールチップ分、余白として改行する --}}
                <br>
            @endif

            @if ($column->title_flag)
                {{-- タイトル指定が設定されている場合、タイトル指定を表示する --}}
                <div class="small text-primary">
                    <i class="fas fa-toggle-on"></i> タイトル指定
                </div>
            @endif
        </td>
    </tr>
@endif

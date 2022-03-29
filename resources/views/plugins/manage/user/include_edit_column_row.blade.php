{{--
 * 項目の更新行
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
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
    {{-- 項目名 --}}
    <td>
        <input class="form-control @if ($errors && $errors->has('column_name_'.$column->id)) border-danger @endif" type="text" name="column_name_{{ $column->id }}" value="{{ old('column_name_'.$column->id, $column->column_name)}}">
    </td>
    {{-- 入力データ型 --}}
    <td>
        <select class="form-control" name="column_type_{{ $column->id }}" style="min-width: 140px;">
            <option value="" disabled>型を指定</option>
            @foreach (UserColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}" @if ($key == old("column_type_$column->id", $column->column_type)) selected="selected" @endif>{{ $value }}</option>
            @endforeach
        </select>
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required_{{ $column->id }}" value="1" @if (old('required_'.$column->id, $column->required) == Required::on) checked="checked" @endif>
    </td>
    {{-- 選択肢の設定ボタン --}}
    <td class="text-center px-2">
        <button
            type="button"
            class="btn btn-success btn-xs cc-font-90 text-nowrap"
            @if (
                $column->column_type == UserColumnType::radio ||
                $column->column_type == UserColumnType::checkbox ||
                $column->column_type == UserColumnType::select
                )
                {{-- 選択肢の設定がない場合のみツールチップを表示 --}}
                @if ($column->select_count == 0)
                    id="detail-button-tip" data-toggle="tooltip" title="選択肢がありません。設定してください。" data-trigger="manual" data-placement="bottom"
                @endif
            @endif
            onclick="location.href='{{url('/')}}/manage/user/editColumnDetail/{{ $column->id }}'"
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

    {{-- 削除ボタン --}}
    <td class="text-center px-2">
        <button class="btn btn-danger cc-font-90 text-nowrap" onclick="javascript:return submit_delete_column({{ $column->id }});"><i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span></button>
    </td>
</tr>
{{-- 選択肢の設定内容の表示行 --}}
@if (
    $column->column_type == UserColumnType::radio ||
    $column->column_type == UserColumnType::checkbox ||
    $column->column_type == UserColumnType::select
    )
    <tr>
        <td class="pt-0 border border-0"></td>
        <td class="pt-0 border border-0" colspan="7">
            @if ($column->select_count > 0)
                {{-- 選択肢データがある場合、カンマ付で一覧表示する --}}
                <i class="far fa-list-alt"></i> {{ $column->select_names }}
            @elseif ($column->select_count == 0)
                {{-- 選択肢データがない場合はツールチップ分、余白として改行する --}}
                <br>
            @endif
        </td>
    </tr>
@endif

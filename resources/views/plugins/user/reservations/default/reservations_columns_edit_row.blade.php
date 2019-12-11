{{--
 * 予約項目の更新行
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
<tr>
    {{-- 表示順操作 --}}
    <td style="vertical-align: middle;" nowrap>
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
        <input class="form-control" type="text" name="column_name_{{ $column->id }}" value="{{ old('column_name_'.$column->id, $column->column_name)}}">
    </td>
    <td>
        {{-- 型 --}}
        <select class="form-control" name="column_type_{{ $column->id }}" style="min-width: 100px;">
            <option value="" disabled>型を指定</option>
            @foreach (ReservationColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}"
                    {{-- 初期表示用 --}}
                    @if($key == $column->column_type)
                        selected="selected"
                    @endif
                    {{-- validation用 --}}
                    @if($key == old("column_type_$column->id"))
                        selected="selected"
                    @endif
                >{{ $value }}</option>
            @endforeach
        </select>
    </td>
    <td style="vertical-align: middle;">
        {{-- 必須 --}}
        <input type="checkbox" name="required_{{ $column->id }}" value="1" @if ($column->required == Required::on) checked="checked" @endif>
    </td>
    {{-- 更新ボタン --}}
    <td style="vertical-align: middle;">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_update_column({{ $column->id }});"><i class="fas fa-save"></i> <span class="d-sm-none">更新</span></button>
    </td>
    {{-- 削除ボタン --}}
    <td style="vertical-align: middle;">
        <button class="btn btn-danger cc-font-90 text-nowrap" onclick="javascript:return submit_delete_column({{ $column->id }});"><i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span></button>
    </td>
</tr>

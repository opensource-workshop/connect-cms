{{--
 * 項目の設定行テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<tr>
    {{-- 表示順 --}}
    <td nowrap>
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
        <input class="form-control" type="text" name="column_name_{{ $column->id }}" value="{{ old('column_name_'.$column->id, $column->column_name)}}">
    </td>
    {{-- 型 --}}
    <td>
        <select class="form-control" name="column_type_{{ $column->id }}" style="min-width: 140px;">
            <option value="" disabled>型を指定</option>
            @foreach (FormColumnType::getMembers() as $key=>$value)
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
    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required_{{ $column->id }}" value="1" @if ($column->required == Required::on) checked="checked" @endif>
    </td>
    {{-- 詳細設定 --}}
    <td class="text-center">
        {{-- 詳細ボタン --}}
        <button 
            type="button" 
            class="btn btn-primary btn-xs cc-font-90 text-nowrap" 
            {{-- 選択肢を保持する項目、且つ、選択肢の設定がない場合のみツールチップを表示 --}}
            @if (
                ($column->column_type == FormColumnType::radio || 
                $column->column_type == FormColumnType::checkbox || 
                $column->column_type == FormColumnType::select) && 
                $column->select_count == 0
                )
                id="detail-button-tip" data-toggle="tooltip" title="選択肢がありません。設定してください。" data-trigger="manual" data-placement="bottom"
            @endif
            {{-- まとめ数を保持する項目、且つ、まとめ数の設定がない場合、ツールチップで設定を促すメッセージを表示 --}}
            @if ($column->column_type == FormColumnType::group && !$column->frame_col)
                id="frame-col-tip" data-toggle="tooltip" title="まとめ数の設定がありません。設定してください。" data-trigger="manual" data-placement="bottom"
            @endif
            onclick="location.href='{{url('/')}}/plugin/forms/editColumnDetail/{{$page->id}}/{{$frame_id}}/{{ $column->id }}#frame-{{$frame->id}}'"
        >
            <i class="far fa-window-restore"></i> <span class="d-sm-none">詳細</span>
        </button>
    </td>
    {{-- 更新ボタン --}}
    <td class="text-center">
        <button 
            class="btn btn-primary cc-font-90 text-nowrap" 
            onclick="javascript:submit_update_column({{ $column->id }});"
        >
            <i class="fas fa-save"></i> <span class="d-sm-none">更新</span>
        </button>
    </td>
    {{-- 削除ボタン --}}
    <td class="text-center">
        <button class="btn btn-danger cc-font-90 text-nowrap" onclick="javascript:return submit_delete_column({{ $column->id }});"><i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span></button>
    </td>
</tr>
{{-- 選択肢の設定内容の表示行 --}}
@if (
    $column->column_type == FormColumnType::radio || 
    $column->column_type == FormColumnType::checkbox || 
    $column->column_type == FormColumnType::select ||
    $column->column_type == FormColumnType::group
    )
    <tr>
        <td class="pt-0 border border-0"></td>
        <td class="pt-0 border border-0" colspan="7">
        
        @if ($column->column_type != FormColumnType::group && $column->select_count > 0)
            {{-- 選択肢データがある場合、カンマ付で一覧表示する --}}
            <i class="far fa-list-alt"></i> 
            {{ $column->select_names }}
        @elseif($column->column_type != FormColumnType::group && $column->select_count == 0)
            {{-- 選択肢データがない場合はツールチップ分、余白として改行する --}}
            <br>
        @endif
        @if ($column->column_type == FormColumnType::group && !isset($column->frame_col))
            {{-- まとめ行でまとめ数の設定がない場合はツールチップ分、余白として改行する --}}
            <br>
        @endif
        </td>
    </tr>
@endif

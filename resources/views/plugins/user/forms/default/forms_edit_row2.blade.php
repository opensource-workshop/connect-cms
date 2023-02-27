{{--
 * 項目の設定行テンプレート（２行）
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
<tr>
    {{-- 表示順 --}}
    <td class="align-middle" rowspan="2" nowrap>
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
    <td colspan="6">
        <small>{{ strip_tags($column->column_name) }}</small>
    </td>
</tr>
<tr>
    {{-- 型 --}}
    <td>
        <select class="form-control" name="column_type_{{ $column->id }}">
            <option value="" disabled>型を指定</option>
            @foreach (FormColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}" @if(old("column_type_{$column->id}", $column->column_type) == $key) selected="selected" @endif>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required_{{ $column->id }}" value="1" data-toggle="tooltip" title="必須項目として指定します。" @if (old("required_{$column->id}", $column->required) == Required::on) checked="checked" @endif>
    </td>
    {{-- 詳細設定 --}}
    <td class="text-center px-2">
        {{-- 詳細ボタン --}}
        <button
            type="button"
            class="btn btn-success btn-xs cc-font-90 text-nowrap"
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
    {{-- コピーボタン --}}
    <td class="text-center px-2">
        <button
            type="button"
            class="btn btn-outline-primary text-nowrap"
            onclick="javascript:submit_copy_column({{ $column->id }});"
        >
            <i class="far fa-copy"></i> <span class="d-sm-none">コピー</span>
        </button>
    </td>
    {{-- 更新ボタン --}}
    <td class="text-center px-2">
        <button
            class="btn btn-primary cc-font-90 text-nowrap"
            onclick="javascript:submit_update_column({{ $column->id }});"
        >
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
    $column->column_type == FormColumnType::radio ||
    $column->column_type == FormColumnType::checkbox ||
    $column->column_type == FormColumnType::select ||
    $column->column_type == FormColumnType::group ||
    $column->caption ||
    $column->place_holder
    )
    <tr>
        <td class="pt-0 border border-0"></td>
        <td class="pt-0 border border-0" colspan="7">

        @if ($column->column_type != FormColumnType::group && $column->select_count > 0)
            {{-- 選択肢データがある場合、カンマ付で一覧表示する --}}
            <div class="small"><i class="far fa-list-alt"></i> {{ $column->select_names }}</div>
        @elseif($column->column_type != FormColumnType::group && !$column->caption && !$column->place_holder && $column->select_count == 0)
            {{-- 選択肢データがなく、キャプション／プレースホルダーの設定もない場合はツールチップ分、余白として改行する --}}
            <br>
        @endif
        @if ($column->caption)
            {{-- キャプションが設定されている場合、キャプションを表示する --}}
            <div class="small {{ $column->caption_color }}"><i class="fas fa-pen"></i> {{ mb_strimwidth($column->caption, 0, 60, '...', 'UTF-8') }}</div>
        @endif
        @if ($column->place_holder)
            {{-- プレースホルダーが設定されている場合、プレースホルダーを表示する --}}
            <div class="small"><i class="fas fa-pen-fancy"></i> {{ mb_strimwidth($column->place_holder, 0, 60, '...', 'UTF-8') }}</div>
        @endif
        @if ($column->column_type == FormColumnType::group && !isset($column->frame_col))
            {{-- まとめ行でまとめ数の設定がない場合はツールチップ分、余白として改行する --}}
            <br>
        @endif
        </td>
    </tr>
@endif

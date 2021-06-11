{{--
 * 項目の設定行テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
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
        <input class="form-control @if ($errors && $errors->has('column_name_'.$column->id)) border-danger @endif" type="text" name="column_name_{{ $column->id }}" value="{{ old('column_name_'.$column->id, $column->column_name)}}">
    </td>

    {{-- 型 --}}
    <td>
        <select class="form-control" name="column_type_{{ $column->id }}">
            <option value="" disabled>型を指定</option>
                @foreach (DatabaseColumnType::getMembers() as $key=>$value)
                    <option value="{{$key}}" @if(old("column_type_$column->id", $column->column_type) == $key) selected="selected" @endif>
                        {{ $value }}
                    </option>
                @endforeach
        </select>
    </td>

    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required_{{ $column->id }}" value="1" data-toggle="tooltip" title="必須項目として指定します。" @if (old("required_{$column->id}", $column->required) == Required::on) checked="checked" @endif>
    </td>

    {{-- 行グループ --}}
    <td class="align-middle text-center">
        {{$column->row_group}}
    </td>

    {{-- 列グループ --}}
    <td class="align-middle text-center">
        {{$column->column_group}}
    </td>

    {{-- 詳細設定 --}}
    <td class="text-center px-2">
        {{-- 詳細ボタン --}}
        <button
            type="button"
            class="btn btn-success btn-xs cc-font-90 text-nowrap"
            {{-- 選択肢を保持する項目、且つ、選択肢の設定がない場合のみツールチップを表示 --}}
            @if (
                ($column->column_type == DatabaseColumnType::radio ||
                $column->column_type == DatabaseColumnType::checkbox ||
                $column->column_type == DatabaseColumnType::select) &&
                $column->select_count == 0
                )
                id="detail-button-tip" data-toggle="tooltip" title="選択肢がありません。設定してください。" data-trigger="manual" data-placement="bottom"
            @endif
            onclick="location.href='{{url('/')}}/plugin/databases/editColumnDetail/{{$page->id}}/{{$frame_id}}/{{ $column->id }}#frame-{{$frame->id}}'"
            >
            <i class="far fa-window-restore"></i>
        </button>
    </td>

    {{-- 更新ボタン --}}
    <td class="text-center px-2">
        <button
            class="btn btn-primary cc-font-90 text-nowrap"
            onclick="javascript:submit_update_column({{ $column->id }});"
            >
            <i class="fas fa-check"></i>
        </button>
    </td>

    {{-- 削除ボタン --}}
    <td class="text-center px-2">
        <button class="btn btn-danger cc-font-90 text-nowrap" onclick="javascript:return submit_delete_column({{ $column->id }});">
            <i class="fas fa-trash-alt"></i>
        </button>
    </td>
</tr>
{{-- 選択肢の設定内容の表示行 --}}
@if (
    $column->column_type == DatabaseColumnType::radio ||
    $column->column_type == DatabaseColumnType::checkbox ||
    $column->column_type == DatabaseColumnType::select ||
    $column->caption ||
    $column->caption_list_detail ||
    $column->title_flag
    )
    <tr>
        <td class="pt-2 border border-0"></td>
        <td class="pt-2 border border-0" colspan="8">
            @if ($column->select_count > 0)
                {{-- 選択肢データがある場合、カンマ付で一覧表示する --}}
                <div class="small">
                    <i class="far fa-list-alt"></i>
                    {{ $column->select_names }}
                </div>
            @elseif(!$column->caption && !$column->title_flag && $column->select_count == 0)
                {{-- 選択肢データがなく、キャプションの設定・タイトル指定もない場合はツールチップ分、余白として改行する --}}
                <br>
            @endif

            @if ($column->caption)
                {{-- キャプションが設定されている場合、キャプションを表示する --}}
                <div class="small {{ $column->caption_color }}">
                    <i class="fas fa-pen"></i>
                    {!! mb_strimwidth($column->caption, 0, 60, '...', 'UTF-8') !!}
                </div>
            @endif

            @if ($column->caption_list_detail)
                {{-- 一覧・詳細キャプションが設定されている場合、キャプションを表示する --}}
                <div class="small {{ $column->caption_list_detail_color }}">
                    <i class="fas fa-th-list"></i>
                    {!! mb_strimwidth($column->caption_list_detail, 0, 60, '...', 'UTF-8') !!}
                </div>
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

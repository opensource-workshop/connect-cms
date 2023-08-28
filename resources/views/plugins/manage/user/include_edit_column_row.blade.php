{{--
 * 項目の更新行
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}
@php
use App\Models\Core\UsersColumns;
@endphp

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
    <td class="align-middle">
        @if ($column->is_fixed_column)
            {{-- 固定項目 --}}
            {{UserColumnType::getDescriptionFixed($column->column_type)}}
            <input type="hidden" name="column_type_{{ $column->id }}" value="{{$column->column_type}}">
        @else
            <select class="form-control" name="column_type_{{ $column->id }}" id="column_type_{{ $column->id }}" style="min-width: 140px;">
                <option value="" disabled>型を指定</option>
                @foreach (UserColumnType::getMembers() as $key=>$value)
                    <option value="{{$key}}" @if ($key == old("column_type_$column->id", $column->column_type)) selected="selected" @endif>{{ $value }}</option>
                @endforeach
            </select>
        @endif
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        @if ($column->is_fixed_column || $column->column_type == UserColumnType::created_at || $column->column_type == UserColumnType::updated_at)
            {{-- 固定項目, 登録日時, 更新日時 --}}
            <input type="hidden" name="required_{{ $column->id }}" @if (old('required_'.$column->id, $column->required) == Required::on) value="1" @else value="0" @endif>
            <input type="checkbox" name="required_{{ $column->id }}" value="1" @if (old('required_'.$column->id, $column->required) == Required::on) checked="checked" @endif disabled>
        @else
            <input type="checkbox" name="required_{{ $column->id }}" value="1" @if (old('required_'.$column->id, $column->required) == Required::on) checked="checked" @endif>
        @endif
    </td>
    {{-- 選択肢の設定ボタン --}}
    <td class="text-center px-2">
        <button
            type="button"
            class="btn btn-success btn-xs cc-font-90 text-nowrap"
            id="button_user_column_detail_{{ $column->id }}"
            @if (UsersColumns::isSelectColumnType($column->column_type))
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
        {{-- 所属が登録されてたら項目の削除はさせない --}}
        @if ($column->column_type == UserColumnType::affiliation && $exists_user_sections)
            <div class="button-wrapper" data-toggle="tooltip" title="{{$column->column_name}}登録済みのユーザがいるため項目を削除できません。">
                <button class="btn btn-danger cc-font-90 text-nowrap" disabled><i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span></button>
            </div>
        @elseif ($column->is_fixed_column)
            {{-- 固定項目 --}}
            <div class="button-wrapper" data-toggle="tooltip" title="ユーザに必ず必要な項目のため削除できません。">
                <button class="btn btn-danger cc-font-90 text-nowrap" disabled><i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span></button>
            </div>
        @else
            <button class="btn btn-danger cc-font-90 text-nowrap" onclick="javascript:return submit_delete_column({{ $column->id }});"><i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span></button>
        @endif
    </td>
</tr>

{{-- 選択肢の設定内容の表示行 --}}
<tr>
    <td class="pt-0 border border-0"></td>
    <td class="pt-0 border border-0" colspan="7">
        <div class="small">
            @if ($column->is_show_auto_regist)
                <span class="text-primary mr-3"><i class="fas fa-toggle-on"></i> 自動ユーザ登録時表示：ON</span>
            @else
                <span class="text-secondary mr-3"><i class="fas fa-toggle-off"></i> 自動ユーザ登録時表示：OFF</span>
            @endif
            @if ($column->is_show_my_page)
                <span class="text-primary mr-3"><i class="fas fa-toggle-on"></i> マイページ表示：ON</span>
            @else
                <span class="text-secondary mr-3"><i class="fas fa-toggle-off"></i> マイページ表示：OFF</span>
            @endif
            @if ($column->is_edit_my_page)
                <span class="text-primary"><i class="fas fa-toggle-on"></i> マイページ編集：ON</span>
            @else
                <span class="text-secondary"><i class="fas fa-toggle-off"></i> マイページ編集：OFF</span>
            @endif
        </div>

        @if ($column->selects->isNotEmpty())
            {{-- 選択肢データがある場合、カンマ付で一覧表示する --}}
            <i class="far fa-list-alt" data-toggle="tooltip" title="選択肢"></i> {{ $column->selects->where('users_columns_id', $column->id)->pluck('value')->implode(',') }}
        @endif

        @if ($column->caption)
            {{-- キャプションが設定されている場合、キャプションを表示する --}}
            <div class="small {{ $column->caption_color }}">
                <i class="fas fa-pen" data-toggle="tooltip" title="キャプション"></i>
                {!! mb_strimwidth($column->caption, 0, 60, '...', 'UTF-8') !!}
            </div>
        @endif

        @if ($column->place_holder)
            {{-- プレースホルダが設定されている場合、プレースホルダを表示する --}}
            <div class="small">
                <i class="fas fa-pen-square" data-toggle="tooltip" title="プレースホルダ"></i>
                {!! mb_strimwidth($column->place_holder, 0, 60, '...', 'UTF-8') !!}
            </div>
        @endif

        @if ($column->use_variable)
            {{-- 変数名を使用する場合、変数名を表示する --}}
            <div class="small">
                <i class="fas fa-box" data-toggle="tooltip" title="変数名"></i>
                {!! mb_strimwidth($column->variable_name, 0, 60, '...', 'UTF-8') !!}
            </div>
        @endif
    </td>
</tr>

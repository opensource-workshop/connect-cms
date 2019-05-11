{{--
 * 設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<tr @if ($delete_flag == '1') class="hidden" @endif>
    <td style="vertical-align: middle;" nowrap>
        @if ($select_flag)
            {{-- 上移動 --}}
            <button type="button" class="btn btn-default btn-xs" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_up({{$row['columns_id']}})">
                <span class="glyphicon glyphicon-arrow-up"></span>
            </button>

            {{-- 下移動 --}}
            <button type="button" class="btn btn-default btn-xs" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_down({{$row['columns_id']}})">
                <span class="glyphicon glyphicon-arrow-down"></span>
            </button>
        @endif
    </td>
    <td>
        <input class="form-control" type="text" name="forms[{{$frame_id}}][{{$row_no}}][column_name]" value="@if($select_flag){{$row['column_name']}}@endif" style="min-width: 150px;">
        {{-- forms_columns テーブルのid を隠しておく。DB更新の際、変更分とわかるようにするため。 --}}
        @if ($select_flag)
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][columns_id]" value="{{$row['columns_id']}}">
        @else
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][columns_id]" value="0">
        @endif

        {{-- 画面上、削除されたことを判定するフラグ。データ削除はフォームの保存時に行うが、どのデータを削除するのかの判定で使用 --}}
        @if ($select_flag)
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][delete_flag]" value="{{$row['delete_flag']}}">
        @else
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][delete_flag]" value="0">
        @endif
    </td>
    <td>
        <select class="form-control" name="forms[{{$frame_id}}][{{$row_no}}][column_type]" style="min-width: 100px;">
            <option value="">項目追加...</option>
            <option value="text"     @if ($select_flag && $row['column_type'] == 'text')     selected @endif>1行文字列型</option>
            <option value="textarea" @if ($select_flag && $row['column_type'] == 'textarea') selected @endif>複数行文字列型</option>
            <option value="radio"    @if ($select_flag && $row['column_type'] == 'radio')    selected @endif>単一選択型</option>
            <option value="checkbox" @if ($select_flag && $row['column_type'] == 'checkbox') selected @endif>複数選択型</option>
            <option value="birthday" @if ($select_flag && $row['column_type'] == 'birthday') selected @endif disabled style="background-color: #f0f0f0;">生年月日型</option>
            <option value="select"   @if ($select_flag && $row['column_type'] == 'select')   selected @endif disabled style="background-color: #f0f0f0;">リストボックス型</option>
            <option value="datetime" @if ($select_flag && $row['column_type'] == 'datetime') selected @endif disabled style="background-color: #f0f0f0;">日付＆時間型</option>
            <option value="file"     @if ($select_flag && $row['column_type'] == 'file')     selected @endif disabled style="background-color: #f0f0f0;">ファイル型</option>
            <option value="group"    @if ($select_flag && $row['column_type'] == 'group')    selected @endif >まとめ行</option>
        </select>
    </td>
    <td style="vertical-align: middle;">
        @if ($row['column_type'] == 'group')
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][required]" value="0">
        @else
            <input type="checkbox" name="forms[{{$frame_id}}][{{$row_no}}][required]" value="1" @if($select_flag && $row['required'] == "1")checked @endif>
        @endif
    </td>
    <td>
        @if ($select_flag && $row['column_type'] == 'group')
            <select class="form-control" name="forms[{{$frame_id}}][{{$row_no}}][frame_col]">
                {{-- <option value="">Choose...</option> --}}
                @for ($i = 1; $i < 5; $i++)
                    <option value="{{$i}}"  @if($select_flag && $row['frame_col']==$i)  selected @endif>{{$i}}</option>
                @endfor
            </select>
        @elseif ($select_flag == 0)
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][frame_col]" value="0">
        @else
            @if ($row['column_type'] == 'radio' || $row['column_type'] == 'checkbox')
                <button class="btn btn-primary form-horizontal" onclick="javascript:return false;" data-toggle="modal" data-target="#formsDetailModal{{$row_no}}"><span class="glyphicon glyphicon-new-window"></span> <span class="hidden-sm hidden-xs">詳細</span></button>
            @endif
        @endif
    </td>
    <td style="vertical-align: middle;">
        @if ($select_flag == 1)
            <button class="btn btn-danger form-horizontal" onclick="javascript:submit_destroy_column({{$row_no}});"><span class="glyphicon glyphicon-trash"></span> <span class="hidden-sm hidden-xs">削除</span></button>
            {{-- <span class="glyphicon glyphicon-trash"> --}}
        @else
            <button class="btn btn-primary form-horizontal" onclick="javascript:submit_setting_column();"><span class="glyphicon glyphicon-pencil"></span> <span class="hidden-sm hidden-xs">追加</span></button>
        @endif
    </td>
</tr>
@if ($select_flag)
    @if ($row['column_type'] == 'radio' || $row['column_type'] == 'checkbox')
    <tr>
        <td style="border-top: none; padding-top: 0;"></td>
        <td style="border-top: none; padding-top: 0;" colspan="2">
        @if (isset($row['select']))
            <span class="glyphicon glyphicon-list-alt"></span> 
            @foreach ($row['select'] as $select)
                {{$select['value']}}
                @if (!$loop->last),@endif
            @endforeach
        @else
            <div class="text-danger"><span class="glyphicon glyphicon-info-sign"></span> 選択肢がありません。設定してください。</div>
        @endif
        </td>
        <td style="border-top: none; padding-top: 0;" colspan="3"></td>
    </tr>
    @endif
@endif

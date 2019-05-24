{{--
 * 設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<tr @if ($delete_flag == '1') class="hidden" @endif>
    <td style="vertical-align: middle;" nowrap>
        {{-- 上移動 --}}
{{--
        <button type="button" class="btn btn-default btn-xs" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_up({{$row['columns_id']}})">
--}}
        <button type="button" class="btn btn-default btn-xs" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_up({{$row_no}})">
            <span class="glyphicon glyphicon-arrow-up"></span>
        </button>

        {{-- 下移動 --}}
{{--
        <button type="button" class="btn btn-default btn-xs" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_down({{$row['columns_id']}})">
--}}
        <button type="button" class="btn btn-default btn-xs" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_down({{$row_no}})">
            <span class="glyphicon glyphicon-arrow-down"></span>
        </button>
    </td>
    <td>
        <input class="form-control" type="text" name="forms[{{$frame_id}}][{{$row_no}}][column_name]" value="{{$row['column_name']}}" style="min-width: 150px;">

        {{-- forms_columns テーブルのid を隠しておく。DB更新の際、変更分とわかるようにするため。 --}}
        <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][columns_id]" value="{{$row['columns_id']}}">

        {{-- 画面上、削除されたことを判定するフラグ。データ削除はフォームの保存時に行うが、どのデータを削除するのかの判定で使用 --}}
        <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][delete_flag]" value="{{$row['delete_flag']}}">
    </td>
    <td>
        <select class="form-control" name="forms[{{$frame_id}}][{{$row_no}}][column_type]" style="min-width: 100px;">
            <option value="">項目追加...</option>
            <option value="text"     @if ($row['column_type'] == 'text')     selected @endif>1行文字列型</option>
            <option value="textarea" @if ($row['column_type'] == 'textarea') selected @endif>複数行文字列型</option>
            <option value="radio"    @if ($row['column_type'] == 'radio')    selected @endif>単一選択型</option>
            <option value="checkbox" @if ($row['column_type'] == 'checkbox') selected @endif>複数選択型</option>
            <option value="birthday" @if ($row['column_type'] == 'birthday') selected @endif disabled style="background-color: #f0f0f0;">生年月日型</option>
            <option value="select"   @if ($row['column_type'] == 'select')   selected @endif disabled style="background-color: #f0f0f0;">リストボックス型</option>
            <option value="datetime" @if ($row['column_type'] == 'datetime') selected @endif disabled style="background-color: #f0f0f0;">日付＆時間型</option>
            <option value="file"     @if ($row['column_type'] == 'file')     selected @endif disabled style="background-color: #f0f0f0;">ファイル型</option>
            <option value="group"    @if ($row['column_type'] == 'group')    selected @endif >まとめ行</option>
        </select>
    </td>
    <td style="vertical-align: middle;">
        @if ($row['column_type'] == 'group')
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][required]" value="0">
        @else
            <input type="checkbox" name="forms[{{$frame_id}}][{{$row_no}}][required]" value="1" @if($row['required'] == "1")checked @endif>
        @endif
    </td>
    <td>
        @if ($row['column_type'] == 'group')
            <select class="form-control" name="forms[{{$frame_id}}][{{$row_no}}][frame_col]">
                {{-- <option value="">Choose...</option> --}}
                @for ($i = 1; $i < 5; $i++)
                    <option value="{{$i}}"  @if($row['frame_col']==$i)  selected @endif>{{$i}}</option>
                @endfor
            </select>
        @else
            @if ($row['column_type'] == 'radio' || $row['column_type'] == 'checkbox')
                <button class="btn btn-primary form-horizontal" onclick="javascript:return false;" data-toggle="modal" data-target="#formsDetailModal{{$row_no}}"><span class="glyphicon glyphicon-new-window"></span> <span class="hidden-sm hidden-xs">詳細</span></button>
            @endif
        @endif
    </td>
    <td style="vertical-align: middle;">
        <button class="btn btn-danger form-horizontal" onclick="javascript:submit_destroy_column({{$row_no}});"><span class="glyphicon glyphicon-trash"></span> <span class="hidden-sm hidden-xs">削除</span></button>
        {{-- <span class="glyphicon glyphicon-trash"> --}}
    </td>
</tr>
@if ($delete_flag == '0')
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

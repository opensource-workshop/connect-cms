{{--
 * 項目の設定行テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<tr @if ($delete_flag == '1') class="d-none" @endif>
    {{-- 表示順 --}}
    <td nowrap>
        {{-- 上移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_up({{$row_no}})">
            <i class="fas fa-arrow-up"></i>
        </button>

        {{-- 下移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_down({{$row_no}})">
            <i class="fas fa-arrow-down"></i>
        </button>
    </td>
    {{-- 項目名 --}}
    <td>
        <input class="form-control" type="text" name="forms[{{$frame_id}}][{{$row_no}}][column_name]" value="{{$row['column_name']}}" style="min-width: 150px;">

        {{-- forms_columns テーブルのid を隠しておく。DB更新の際、変更分とわかるようにするため。 --}}
        <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][columns_id]" value="{{$row['columns_id']}}">

        {{-- 画面上、削除されたことを判定するフラグ。データ削除はフォームの保存時に行うが、どのデータを削除するのかの判定で使用 --}}
        <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][delete_flag]" value="{{$row['delete_flag']}}">
    </td>
    {{-- 型 --}}
    <td>
        <select class="form-control" name="forms[{{$frame_id}}][{{$row_no}}][column_type]" style="min-width: 100px;">
            <option value="">項目追加...</option>
            <option value="text"     @if ($row['column_type'] == 'text')     selected @endif>1行文字列型</option>
            <option value="textarea" @if ($row['column_type'] == 'textarea') selected @endif>複数行文字列型</option>
            <option value="radio"    @if ($row['column_type'] == 'radio')    selected @endif>単一選択型</option>
            <option value="checkbox" @if ($row['column_type'] == 'checkbox') selected @endif>複数選択型</option>
            <option value="select"   @if ($row['column_type'] == 'select')   selected @endif>リストボックス型</option>
            <option value="mail"     @if ($row['column_type'] == 'mail')     selected @endif>メールアドレス型</option>
            <option value="birthday" @if ($row['column_type'] == 'birthday') selected @endif disabled style="background-color: #f0f0f0;">生年月日型</option>
            <option value="datetime" @if ($row['column_type'] == 'datetime') selected @endif disabled style="background-color: #f0f0f0;">日付＆時間型</option>
            <option value="file"     @if ($row['column_type'] == 'file')     selected @endif disabled style="background-color: #f0f0f0;">ファイル型</option>
            <option value="group"    @if ($row['column_type'] == 'group')    selected @endif >まとめ行</option>
        </select>
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        @if ($row['column_type'] == 'group')
            <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][required]" value="0">
        @else
            <input type="checkbox" name="forms[{{$frame_id}}][{{$row_no}}][required]" value="1" @if($row['required'] == "1")checked @endif>
        @endif
    </td>
    {{-- 詳細ボタン --}}
    <td class="text-center">
        @if ($row['column_type'] == 'group')
            <select class="form-control" name="forms[{{$frame_id}}][{{$row_no}}][frame_col]">
                {{-- <option value="">Choose...</option> --}}
                @for ($i = 1; $i < 5; $i++)
                    <option value="{{$i}}"  @if($row['frame_col']==$i)  selected @endif>{{$i}}</option>
                @endfor
            </select>
        @else
            @if ($row['column_type'] == 'radio' || $row['column_type'] == 'checkbox' || $row['column_type'] == 'select')
                <button class="btn btn-primary btn-xs cc-font-90 text-nowrap" onclick="javascript:return false;" data-toggle="modal" data-target="#formsDetailModal{{$row_no}}"><i class="far fa-window-restore"></i> <span class="d-sm-none">詳細</span></button>
            @endif
        @endif
    </td>
    {{-- 削除ボタン --}}
    <td class="text-center">
        <button class="btn btn-danger cc-font-90 text-nowrap" onclick="javascript:submit_destroy_column({{$row_no}});"><i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span></button>
    </td>
</tr>
{{-- 選択肢の設定内容の表示行 --}}
@if ($delete_flag == '0')
    @if ($row['column_type'] == 'radio' || $row['column_type'] == 'checkbox' || $row['column_type'] == 'select')
    <tr>
        <td class="pt-0 border border-0"></td>
        <td class="pt-0 border border-0" colspan="2">
        @if (isset($row['select']))
            <i class="far fa-list-alt"></i> 
            @foreach ($row['select'] as $select)
                {{$select['value']}}
                @if (!$loop->last),@endif
            @endforeach
        @else
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> 選択肢がありません。設定してください。</div>
        @endif
        </td>
        <td style="border-top: none; padding-top: 0;" colspan="3"></td>
    </tr>
    @endif
@endif

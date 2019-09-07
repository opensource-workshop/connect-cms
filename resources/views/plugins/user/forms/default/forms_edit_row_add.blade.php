{{--
 * 設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<tr id="column_add_tr">
    <td style="vertical-align: middle;" nowrap><br /></td>
    <td>
        <input class="form-control" type="text" name="forms[{{$frame_id}}][{{$row_no}}][column_name]" value="" style="min-width: 150px;">
        {{-- forms_columns テーブルのid を隠しておく。DB更新の際、変更分とわかるようにするため。 --}}
        <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][columns_id]" value="0">

        {{-- 画面上、削除されたことを判定するフラグ。データ削除はフォームの保存時に行うが、どのデータを削除するのかの判定で使用 --}}
        <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][delete_flag]" value="0">
    </td>
    <td>
        <select class="form-control" name="forms[{{$frame_id}}][{{$row_no}}][column_type]" style="min-width: 100px;">
            <option value="">項目追加...</option>
            <option value="text">1行文字列型</option>
            <option value="textarea">複数行文字列型</option>
            <option value="radio">単一選択型</option>
            <option value="checkbox">複数選択型</option>
            <option value="birthday" disabled style="background-color: #f0f0f0;">生年月日型</option>
            <option value="select"   disabled style="background-color: #f0f0f0;">リストボックス型</option>
            <option value="datetime" disabled style="background-color: #f0f0f0;">日付＆時間型</option>
            <option value="file"     disabled style="background-color: #f0f0f0;">ファイル型</option>
            <option value="group">まとめ行</option>
        </select>
    </td>
    <td style="vertical-align: middle;">
        <input type="checkbox" name="forms[{{$frame_id}}][{{$row_no}}][required]" value="1">
    </td>
    <td>
        <input type="hidden" name="forms[{{$frame_id}}][{{$row_no}}][frame_col]" value="0">
    </td>
    <td style="vertical-align: middle;">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_setting_column();"><i class="fas fa-plus"></i> <span class="hidden-sm hidden-xs">追加</span></button>
    </td>
</tr>

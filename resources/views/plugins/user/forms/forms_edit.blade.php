{{--
 * 設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}

@auth
{{-- カラムの追加 --}}
{{--
<form action="{{URL::to($page->permanent_link)}}?action=confirm&frame_id={{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="return_mode" value="edit">
    <div class="col-sm-3" style="padding-right: 0; margin-bottom: 5px;">
        <select name="add_plugin" class="form-control" onchange="javascript:form_add_column{{$frame_id}}.submit();">
            <option value="">フォームの項目追加...</option>
            <option value="text">・1行文字列型</option>
            <option value="textarea">・複数行文字列型</option>
            <option value="radio">・単一選択型</option>
            <option value="checkbox">・複数選択型</option>
            <option value="birthday" disabled style="background-color: #f0f0f0;">・生年月日型</option>
            <option value="select" disabled style="background-color: #f0f0f0;">・リストボックス型</option>
            <option value="datetime" disabled style="background-color: #f0f0f0;">・日付＆時間型</option>
            <option value="file" disabled style="background-color: #f0f0f0;">・ファイル型</option>
        </select>
    </div>
</form>
--}}

<script type="text/javascript">
    {{-- 項目追加のsubmit JavaScript --}}
    function submit_setting_column() {
        form_columns.action = "/plugin/forms/settingColumn/{{$page->id}}/{{$frame_id}}";
        form_columns.submit();
    }

    {{-- 項目削除のsubmit JavaScript --}}
    function submit_destroy_column(row_no) {
        form_columns.action = "/plugin/forms/destroyColumn/{{$page->id}}/{{$frame_id}}";
        form_columns.destroy_no.value = row_no;
        form_columns.submit();
    }

    {{-- ページの上移動用フォームのsubmit JavaScript --}}
    function submit_sequence_up( id ) {
        form_columns.action = "/plugin/forms/sequenceUp/{{$page->id}}/{{$frame_id}}/" + id;
        form_columns.submit();
    }

    {{-- ページの下移動用フォームのsubmit JavaScript --}}
    function submit_sequence_down( id ) {
        form_columns.action = "/plugin/forms/sequenceDown/{{$page->id}}/{{$frame_id}}/" + id;
        form_columns.submit();
    }
</script>

{{-- キャンセル用のフォーム。キャンセル時はセッションをクリアするため、トークン付きでPOST でsubmit したい。 --}}
<form action="/plugin/forms/cancel/{{$page->id}}/{{$frame_id}}" name="forms_cancel" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
    {{ csrf_field() }}
</form>

<!-- Add or Update Form Button -->
<div class="form-group">
    <form action="/plugin/forms/save/{{$page->id}}/{{$frame_id}}" id="form_columns" name="form_columns" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="forms_id" value="{{$forms_id}}">
        <input type="hidden" name="destroy_no" value="">
        <input type="hidden" name="return_mode" value="edit">

        <div class="panel panel-info">
            <div class="panel-heading">項目設定</div>

            {{-- カラムの一覧 --}}
            <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>操作</th>
                    <th>項目名</th>
                    <th>型</th>
                    <th>必須</th>
                    <th>まとめ数</th>
                    <th>サイズ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    @include('plugins.user.forms.forms_edit_row',['select_flag' => 1, 'row_no' => $loop->iteration, 'delete_flag' => $row['delete_flag']])
                @endforeach
                <tr>
                    <th colspan="4">【項目の追加行】</th>
                </tr>
                @include('plugins.user.forms.forms_edit_row',['select_flag' => 0, 'row_no' => 0, 'delete_flag' => 0])
                </tr>
            </tbody>
            </table>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary form-horizontal">
                フォーム保存
            </button>
{{--            <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'">キャンセル</button> --}}
            <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="javascript:forms_cancel.submit();">キャンセル</button>
        </div>
    </form>
</div>

@endauth
